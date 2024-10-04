<?php

$baseDir = ROOT . "/extensions/autobackup";

$loadDirs = [
    "$baseDir/models/storages/",
];

foreach ($loadDirs as $loadDir) {
    foreach (glob($loadDir . '*.php') as $file) {
        //var_dump($file);
        require_once $file;
    }
}

