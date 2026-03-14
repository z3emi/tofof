<?php
$log = file_get_contents('storage/logs/laravel.log');
$lines = explode("\n", $log);
$recentLines = array_slice($lines, -100);
echo implode("\n", $recentLines);
