#!/usr/local/bin/php
<?php
require_once 'local.inc';

/** @var string $command */
$command = null;

if ($_SERVER["argc"]>=2) {
    $command = $_SERVER["argv"][1];
}

$daemon = new Daemon(LOG_PATH, RUN_PATH);

if (!$daemon->start(MY_IP, MY_PORT, $command)) {
    throw new Exception('Cannot daemon start');
}

