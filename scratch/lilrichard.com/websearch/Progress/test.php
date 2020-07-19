<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/28/18
 * Time: 5:06 PM
 */

const DS = DIRECTORY_SEPARATOR;

if (!file_exists('./stem')){
    if (!file_exists('./stem.cpp')){
        print "\n\nStemming Algorithm Not Found!\n\n" and die;
    }
    `g++ -o stem stem.cpp`;
    if (!file_exists('./stem')){
        print "\n\nStemming Algorithm Could Not Be Compiled!\n\n" and die;
    }
}


$test = [
    'caresses' => 'caress',
    'ponies' => 'poni',
    'ties' => 'ti',
    'caress' => 'caress',
    'cats' => 'cat',
    'feed' => 'feed',
    'agreed' => 'agree',
    'disabled' => 'disable',
    'matting' => 'mat',
    'mating' => 'mate',
    'meeting' => 'meet',
    'milling' => 'mill',
    'messing' => 'mess',
    'meetings' => 'meet'
];

function stem ($string) {
    return `./stem $string`;
}

foreach ($test as $item => $value) {
    print "$item \t=> \t$value \t\t" . ($c = (($stem = trim(stem($item))) === $value ? 'PASS' : 'FAIL')) . "\t\t" . $stem. PHP_EOL;
}



