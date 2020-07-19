<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/28/18
 * Time: 2:56 PM
 */

// lets use three languages


class SuperScanner
{

    public const DS = DIRECTORY_SEPARATOR;
    public const UPLINE = "\033[F";
    public const DOWNLINE = "\033[E";
    public const FOLDER = 'ft911';

    public $status;
    public $files;

    public function __construct()
    {

        if (!file_exists('stem')) {
            if (!file_exists('./stem.cpp')) {
                print "\n\nStemming Algorithm Not Found!\n\n" and die;
            }

            `g++ -o stem stem.cpp`;
            if (!file_exists('stem')) {
                print "\n\nStemming Algorithm Could Not Be Compiled!\n\n" and die;
            }
        }

        $folder = self::FOLDER;

        $directory = `ls ./$folder`;

        $this->files = explode(PHP_EOL, $directory);

        $threads_completed =
        $files_completed =
        $documents_completed =
        $tags_completed =
        $words_completed = 0;

        $files_found = substr_count($this->files);

        $threads_started =
        $documents_found =
        $tags_found =
        $words_found = 1;

        $this->status = [
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

    }


    public function __invoke()
    {

        print ($files_found = count($this->files)) . " Files To Scan\n\n";

        usleep(10000);

        $forks = 1;


        if (!function_exists('pcntl_fork')) {
            die('PCNTL functions not available on this PHP installation');
        }


        foreach ($files as $value) {

            bar($status['Files'], 30);

            $file = file_get_contents(__DIR__ . DS . $folder . DS . $value);

            $documents_in_file = explode('<DOC>', $file);

            $documents_found = count($documents_in_file);
            $documents_completed = 0;


            foreach ($documents_in_file as $doc => $contents) {

                bar($status['Documents'], 30);

                if (empty($contents)) {
                    $documents_found--;
                    print UP_LINE;
                    continue;
                }


                $xml = simplexml_load_string('<DOC>' . $contents);

                $json = json_encode($xml);

                $json = json_decode($json, TRUE);

                if (!is_array($json)) {
                    print "Failed to Parse Document\n\n";
                    die(0);
                }

                $tags_found = count($json);
                $tags_completed = 0;

                foreach ($json as $tag => $string) {

                    bar($status['Tags'], 30);

                    $string = preg_replace('/[^A-Za-z ]/', ' ', trim($string));

                    $string = explode(' ', $string);

                    $words_found = count($string);
                    $words_completed = 0;

                    foreach ($string as $word) {

                        bar($status['Words'], 30);

                        if (empty($word) || strlen($word) < 3) {
                            $words_found--;
                            print UP_LINE;
                            continue;
                        }

                        $word = trim(`./stem $word`);       // Run the words through our C++ stemming algorithm

                        $hashTable[$word][$json['DOCNO']][] = $words_completed++;

                        print UP_LINE;

                    }
                    $tags_completed++;
                    print UP_LINE;
                }

                $documents_completed++;
                print UP_LINE;


                if ($documents_completed > 20) {
                    print_r($hashTable);
                    exit(1);

                }

            }

            $files_completed++;
            print UP_LINE;

            print_r($hashTable);

            exit(1);

        }
    }

    public function bar(array $status, int $size)
    {

        [$name, $done, $total] = $status;

        static $start_time;

        if (empty($start_time)) {
            $start_time = time();
        }


        if (!is_int($total) || !is_int($done)) {
            print "No INT? $total .. $done\n\n";
            return null;
        }

        // if we go over our bound, just ignore it
        if ($done > $total) {
            return null;
        }

        if ($total > 0) {
            $perc = (double)($done / $total);
        } else {
            $perc = 100;
        }

        $bar = floor($perc * $size);

        $status_bar = '[';

        $status_bar .= str_repeat('=', $bar);

        if ($bar < $size) {
            $status_bar .= '>';
            $status_bar .= str_repeat(' ', $size - $bar);
        } else {
            $status_bar .= '=';
        }

        $disp = number_format($perc * 100, 0);

        $status_bar .= "] \t['$name'] $disp%  $done/$total";

        $now = time();

        if ($done !== 0) {
            $rate = ($now - $start_time) / $done;
        } else {
            $rate = 0;
        }

        $status_bar .= ' remaining: ' . number_format(round($rate * ($total - $done), 2)) . ' sec.  elapsed: ' . number_format($now - $start_time) . ' sec.' . PHP_EOL;


        print $status_bar;


        // when done, send a newline
        #if ($done === $total) {
        #    print "\n";
        #}
    }


}