<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 3/7/18
 * Time: 6:35 PM
 */

function fork_process(int $size, callable $cb, ...$children)
{
    $count = count($children);
    $shared_memory_monitor = shmop_open(ftok(__FILE__, chr(0)), "c", 0644, $count);
    $shared_memory_ids = (object)array();

    try {
        for ($i = 1; $i <= $count; $i++) {
            $shared_memory_ids->$i = shmop_open(ftok(__FILE__, chr($i)), "c", 0644, $size);
        }

        ##################################  Fork each process with a shared memory buffer
        for ($i = 1; $i <= $count; $i++) {
            switch (pcntl_fork()) {
                case -1:
                case 0:
                    if ($i === 1) {
                        usleep(100000);
                    }
                    $shared_memory_data = $children[$i - 1]();
                    shmop_write($shared_memory_ids->$i, $shared_memory_data, 0);
                    shmop_write($shared_memory_monitor, '1', $i - 1);
                    exit($i);

                default:
            }
        }
        while (pcntl_waitpid(0, $status) !== -1) {
            if (shmop_read($shared_memory_monitor, 0, $count) === str_repeat('1', $count))   // Check to see that all process are complete
            {
                $result = array();
                foreach ($shared_memory_ids as $key => $value) {
                    $result[$key - 1] = shmop_read($shared_memory_ids->$key, 0, $size);
                    shmop_delete($shared_memory_ids->$key);
                }
                shmop_delete($shared_memory_monitor);
                $cb($result);
            }
        }
    } catch (Exception | Error $e) {
        if (is_iterable($shared_memory_ids)) {
            foreach ($shared_memory_ids as $key => $value) {
                $result[$key - 1] = shmop_read($shared_memory_ids->$key, 0, $size);
                shmop_delete($shared_memory_ids->$key);
            }
        }
        print $e->getMessage() . PHP_EOL;
    }
}

fork_process(1024 ** 2,
    function (array $result) {
        foreach ($result as $value) {
            print $value;
        }
        print PHP_EOL . PHP_EOL;
    }, function () {
        return 'Hello ';
    }, function () {
        return 'World!';
    }, function () {
        return 'Step 3';
    });
