<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/28/18
 * Time: 2:56 PM
 */


set_error_handler(function () { /* ignore errors */
});

##################################### For Gigs for memory
ini_set('memory_limit', '4056M');

defined('AJAX') or define('AJAX', false);

return new class {
    public const DS = DIRECTORY_SEPARATOR;
    public const UP_LINE = "\033[F";
    public const DOWN_LINE = "\033[E";

    private $progress;
    private $Forward;
    private $Inverted;

    /**
     *  use g++ to compile the c code for our machine
     */
    public function compile(): void
    {
        if (!file_exists('./stem.cpp')) {
            print "\n\nStemming Algorithm Not Found!\n\n" and die;
        }

        `g++ -o stem stem.cpp`;
        if (!file_exists('./stem')) {
            print "\n\nStemming Algorithm Could Not Be Compiled!\n\n" and die;
        }
    }

    /**
     * Use the bash command ls to get all contents of a folder
     * @param string $folder
     * @return array
     */
    public function searchFolder(string $folder = 'ft911'): array
    {
        if (!file_exists($folder = __DIR__ . DIRECTORY_SEPARATOR . $folder)) {
            print 'Could not locate folder to scan' and die;
        }
        $files = array_filter(explode(PHP_EOL, `ls "$folder"`));
        foreach ($files as $pos => $file) {
            $files[$pos] = $folder . DIRECTORY_SEPARATOR . $file;
        }
        return $files;
    }


    /**
     * fork a process and wait. All parents
     * @param callable $cb
     * @param array $children
     * @return mixed
     */
    public function fork_process(callable $cb, array $children)
    {
        try {
            $count = count($children);

            $shared_memory_monitor = shmop_open(ftok(__FILE__, chr(0)), "c", 0644, $count);

            for ($i = 1; $i <= $count; $i++) {
                switch (pcntl_fork()) {
                    case -1:
                    case 0:
                        if ($i === 1) {
                            usleep(100000);
                        }
                        $children[$i - 1]();
                        shmop_write($shared_memory_monitor, '1', $i - 1);
                        exit($i);
                    default:
                }
            }
            while (pcntl_waitpid(0, $status) !== -1) {

                $this->progress['Processes'][1] = shmop_read($shared_memory_monitor, 0, $count);

                if ($this->progress['Processes'][1] === str_repeat('1', $count))   // Check to see that all process are complete
                {
                    shmop_delete($shared_memory_monitor);
                    return $cb();
                }
            }
        } catch (Exception | Error $e) {
            shmop_delete($shared_memory_monitor ?? false);
            print $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * explode on the XML DOC tag
     * @param string $text
     * @return array
     */
    public function separate_Documents(string $text): array
    {
        return array_filter(explode('<DOC>', $text));
    }

    /** Use xml built in functions to array'ify
     * @param string|array $contents
     * @return array|SimpleXMLElement|string
     */
    public function xml_parse_document(string $contents)
    {

        $contents = explode('<DOC>', $contents);

        foreach ($contents as $key => $document) {
            $xml = simplexml_load_string('<DOC>' . $document);
            $xml = json_encode($xml);
            $xml = array_filter(json_decode($xml, TRUE));
            $contents[$key] = $xml;
        }

        if (!is_array($xml)) {
            print "Failed to Parse Document\n\n";
            die(0);
        }

        return $xml;
    }

    /** Remove un-indexed text
     * @param string $text
     * @return array
     */
    public function clean_Text(string $text): array
    {
        global $words_found;

        // /\bs\S+/ig
        $text = preg_replace('/[.,_\-\'"]/', ' ', $text);   // remove
        $text = preg_replace('/[^A-Za-z ]/', '', $text);

        $words = array_filter(explode(' ', $text));

        $words_found += count($words);

        return $words;
    }

    /** build the forward and backwards index using loops and our c program
     * @param array $documents_in_file
     */
    public function buildIndex(array $documents_in_file): void
    {
        $stopWords = explode(PHP_EOL, file_get_contents('./stopwordlist.txt')); // TODO - add to member of class

        $Forward = $Inverted = $Porter = [];

        $this->progress['Documents'][2] = \count($documents_in_file);

        foreach ($documents_in_file as $num => $contents) {

            $this->progress['Documents'][1]++;

            $this->status_bar($this->progress['Documents'], 30);

            $xml = $this->xml_parse_document($contents);

            $words_completed = 0;

            $words = $this->clean_Text($xml['TEXT']);

            foreach ($words as $word) {

                if (in_array($word, $stopWords, false)) {
                    continue;
                }

                if (!$stem = array_search($word, $Porter, false)) {
                    $Porter[$word] = $stem = trim(`./stem $word`);       // Run the words through our C++ stemming algorithm
                }

                $Forward[$xml['DOCNO']][$stem] =
                    ($Forward[$xml['DOCNO']][$stem] ?? false) ?
                        ++$Forward[$xml['DOCNO']][$stem] : 1;

                $Inverted[$word][$xml['DOCNO']][] = $words_completed++;

            }

        }

        file_put_contents('./database/forward/' . getmypid() . '.forward.txt', json_encode($Forward));
        file_put_contents('./database/inverted/' . getmypid() . '.inverted.txt', json_encode($Inverted));

    }

    /** separate our documents into a reasonable number of processes
     * @param array $documents
     * @return array
     */
    public function multiProcess(array $documents): array
    {

        $count = \count($documents);

        $processes = [];

        /** My computer only allows me to have 8 file descriptors open at a time per
         *  program execution. This works like named pipes I suppose, but has also given me
         *  more trouble than named pipes. I've never opened >2 named
         *  pipes in php, but it may work as expected. (later) For now I choose to section off
         *  six processes to get the job done.
         */

        $cut = ceil($count / 20);


        for ($i = 0; $i <= 20 && !empty($documents); $i++) {
            $subset = array_splice($documents, 0, $cut);
            if (empty($subset)) {
                continue;
            }
            $this->progress['Processes'][2]++;

            $processes[$i] = function () use ($subset) {
                $this->buildIndex($subset);
            };
        }

        return $processes;
    }

    /**
     *  constructor.
     */
    public function __construct()
    {
        if (file_exists('./database/forward.json') && file_exists('./database/inverted.json')) {
            if ('n' === strtolower(readline(PHP_EOL . 'Use previously generated indexed? ([y,n]) :  '))) {
                `rm ./database/forward.json`;
                `rm ./database/inverted.json`;
                `rm -r ./database/forward/`;
                `rm -r ./database/inverted/`;
            } else {
                $this->Inverted = json_decode(file_get_contents('./database/inverted.json'), true);
                $this->search();
                exit(1);
            }
        }

        if (!file_exists('./stem')) {
            $this->compile();
        }

        if (!@mkdir('./database') && !is_dir('./database')) {
            print 'Failed to create our local storage' . PHP_EOL and die;
        }

        if (!@mkdir('./database/forward') && !is_dir('./database/forward')) {
            print 'Failed to create our local storage' . PHP_EOL and die;
        }

        if (!@mkdir('./database/inverted') && !is_dir('./database/inverted')) {
            print 'Failed to create our local storage' . PHP_EOL and die;
        }

        // This should help keep our references
        global $threads_completed, $files_completed, $documents_completed,
               $tags_completed, $words_completed, $threads_started, $files_found,
               $documents_found, $tags_found, $words_found;


        $threads_completed =
        $files_completed =
        $documents_completed =
        $tags_completed =
        $words_completed = 0;

        $threads_started =
        $files_found =
        $documents_found =
        $tags_found =
        $words_found = 1;

        $this->progress = [
            'Processes' => [
                'Processes Completed',
                &$threads_completed,
                &$threads_started
            ],
            'Files' => [
                'Files Scanned',
                &$files_completed,
                &$files_found
            ],
            'Documents' => [
                'Documents Parsed',
                &$documents_completed,
                &$documents_found
            ],
            'Tags' => [
                'Tags Found',
                &$tags_completed,
                &$tags_found
            ],
            'Words' => [
                'Words Stemmed',
                &$words_completed,
                &$words_found
            ],
        ];

        $time = microtime(true);

        ###################################### Get Documents To Parse
        $files = $this->searchFolder();

        $fullText = '';

        ###################################### Concatenate all text in folder
        foreach ($files as $file) {
            if (empty($file)) {
                $files_found--;
                continue;
            }
            $fullText .= file_get_contents($file);
        }

        ##################################### Separate XML docs in concatenated text
        $documents_in_file = $this->separate_Documents($fullText);

        print self::DOWN_LINE;

        ##################### Split Processes
        $superSplit = $this->multiProcess($documents_in_file);

        ##################### Build our index
        [$Forward, $Inverted] = $this->fork_process(
            function () {
                $f = $i = [];

                $Forward = $this->searchFolder('database/forward');
                foreach ($Forward as $file) {
                    if (empty($file)) {
                        continue;
                    }
                    $file = json_decode(file_get_contents($file), true);
                    $f = array_merge_recursive($f, $file);
                }

                $Inverted = $this->searchFolder('database/inverted');
                foreach ($Inverted as $file) {
                    if (empty($file)) {
                        continue;
                    }
                    $file = json_decode(file_get_contents($file), true);
                    $i = array_merge_recursive($i, $file);
                }
                return [$f, $i];
            }, $superSplit);

        file_put_contents('./database/forward.json', json_encode($Forward));
        file_put_contents('./database/inverted.json', json_encode($Inverted));

        ##################### Start our Search Engine
        // @system('clear');  // one of these will fail, but it's better this way.. trust me
        #@system('cls');

        $time = number_format(microtime(true) - $time);

        print "Done in $time seconds\n\n\n";

        print 'To exit this program enter `0`' . PHP_EOL;

        $this->Forward = $Forward;
        $this->Inverted = $Inverted;

        $this->search();

    }

    /**
     *  Search the inverted index
     */
    public function search(): void
    {
        $word = trim(readline(PHP_EOL . 'Search Indexes: '));
        while ($word !== '0') {

            @system('clear');  // one of these will fail, but it's better this way.. trust me
            if (array_key_exists($word, $this->Inverted)) {
                print $word . ' => ';
                print_r($this->Inverted[$word]);
            } else {
                print "$word was not found in the collection!" . PHP_EOL;
            }
            $word = readline(PHP_EOL . 'Search Indexes: ');
        }
        print 'later!' . PHP_EOL . PHP_EOL . PHP_EOL;
    }

    /** if the collection is F***n huge, this is helpful
     * @param array $status
     * @param int $size
     */
    public function status_bar(array $status, int $size): void
    {
        [$name, $done, $total] = $status;

        static $start_time;


        if (empty($start_time)) {
            $start_time = microtime(true);
        }

        // if we go over our bound, just ignore it
        if ($done > $total) {
            return;
        }

        if ($total > 0) {
            $perc = (double)($done / $total);
        } else {
            $perc = 100;
        }

        $bar = floor($perc * $size);

        $status_bar = "\r[";

        $status_bar .= str_repeat('=', $bar);

        if ($bar < $size) {
            $status_bar .= '>';
            $status_bar .= str_repeat(' ', $size - $bar);
        } else {
            $status_bar .= '=';
        }

        $disp = number_format($perc * 100, 0);

        $status_bar .= "]\t['$name'] $disp%  $done/$total";

        $now = microtime(true);

        if ($perc < 2 || $done !== 0) {
            $rate = ($now - $start_time) / $done;
        } else {
            $rate = 0;
        }

        $status_bar .= ' remaining: ' . number_format(round($rate * ($total - $done), 2)) . ' sec.  elapsed: ' . number_format($now - $start_time) . ' sec.';


        print $status_bar;

        // when done, send a newline
        #if ($done === $total) {
        #    print "\n";
        #}

    }
};