<?php

/*
    DB Log Merge tool for vTiger
    Author: Hieu Nguyen
    Date: 2018-12-20
    Usage: run this script and follow the instruction
*/

// Prepare
$startTime = microtime(true);
$outputDir = 'mergelogs/output';
$tempFile = $outputDir . '/temp.log';
$outputFile = $outputDir . '/output.log';

if(!file_exists($outputDir)) {
    mkdir($outputDir, 0777, true);
}

$logFileCount = count(glob('mergelogs/*.log'));

if($logFileCount == 0) {
    echo 'Please put your log files into folder <b>mergelogs</b>!';
    exit;
}
else if($logFileCount == 1) {
    echo 'Only 1 log file found inside folder <b>mergelogs</b>. Nothing to do.';
    exit;
}

echo 'Found '. $logFileCount .' log files. Starting...<br/>';

file_put_contents($tempFile, '');

foreach(glob('mergelogs/*.log') as $file) {
    $content = file_get_contents($file);
    file_put_contents($tempFile, $content, FILE_APPEND);
}

$lines = file($tempFile);
$log = array();
$conflicts = array();
$index;

// Process
foreach($lines as $line) {
    $line = trim($line);

    if(!empty($line)) {
        if(strpos($line, '/*') !== false) {
            preg_match('/\d+\.\d+/', $line, $matches);
            $index = $matches[0];
            if(strlen($index) < 15) $index = str_pad($index, 15, '0');

            if(empty($log[$index])) {
                $log[$index] = $line;
            } 
            else {
                $log[$index] .= "\r\n" . $line;
                $conflicts[] = $index;
            }
        }
        else {
            $log[$index] .= "\r\n" . $line;
        }
    }
}

// Sort
ksort($log, SORT_STRING);
file_put_contents($outputFile, join("\r\n\r\n", $log));

// Output
$endTime = microtime(true);
$processTime = ($endTime - $startTime) / 1000000;

echo 'Processed in '. sprintf('%f', $processTime) .' seconds!<br/>';
echo 'Download <a href="'. $outputFile .'">output.log</a><br/>';

if(!empty($conflicts)) {
    echo '<font color="red">Warning! There are conflicts in the log: ' . join(', ', $conflicts) . '</font>';
}