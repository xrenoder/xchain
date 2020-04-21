#!/usr/local/bin/php
<?php
require_once 'local.inc';

$app = new App(SCRIPT_NAME);
Logger::create($app,LOG_PATH, 'xchain.log', 'error.log', 'php.err');
Server::create($app,MY_IP, MY_PORT);
Daemon::create($app,LOG_PATH, RUN_PATH);

$command = '';

if ($_SERVER['argc'] >= 2) {
    $command = $_SERVER['argv'][1];
}

$app->run($command);

