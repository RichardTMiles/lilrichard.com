<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 4/10/18
 * Time: 9:32 PM
 */


ini_set('memory_limit', '4056M');

function searchFolder(string $folder): array
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

$f = $i = [];
$Forward = searchFolder('database/forward');
foreach ($Forward as $file) {
    if (empty($file)) {
        continue;
    }
    $i = array_merge_recursive($i, json_decode(file_get_contents("$file"),true));
}
$Inverted  = searchFolder('database/inverted');
foreach ($Inverted as $file) {
    if (empty($file)) {
        continue;
    }
    $f = array_merge_recursive($f, json_decode(file_get_contents("$file"),true));
}


file_put_contents('./database/forward.json', json_encode($i));
file_put_contents('./database/inverted.json', json_encode($f));
