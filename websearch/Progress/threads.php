<?php



for ($x = 1; $x < 5; $x++) {
    switch ($pid = pcntl_fork()) {
        case -1:
            // @fail
            die('Fork failed');
            break;
        case 0:
            // @child: Include() misbehaving code here
            print "FORK: Child #{$x} preparing to nuke...\n";

            generate_fatal_error(); // Undefined function
            break;

        default:
            // @parent
            print "FORK: Parent, letting the child run amok...\n";
            pcntl_waitpid($pid, $status);
            break;
    }
}

print "Done! :^)\n\n";