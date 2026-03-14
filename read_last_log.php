<?php
$log = file_get_contents('storage/logs/laravel.log');
// Find the last occurrence of '[20' which is usually the start of a log entry
$pos = strrpos($log, '[20');
if ($pos !== false) {
    echo substr($log, $pos, 1000); // print the first 1000 chars of the last log entry
} else {
    echo "No log entries found starting with [20";
}
