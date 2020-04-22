#!/usr/local/bin/php
<?php
require_once 'local.inc';

$app = new App(SCRIPT_NAME);
Logger::create($app,LOG_PATH, Logger::DBG_SERV, 'xchain.log', 'error.log', 'php.err');
Server::create($app,MY_IP, MY_PORT);
Daemon::create($app, RUN_PATH,  'pid');

$command = '';

if ($_SERVER['argc'] >= 2) {
    $command = $_SERVER['argv'][1];
}

try {
    if (!$app->getDaemon()->run($command)) {
        throw new Exception('Cannot daemon start');
    }

    $app->getServer()->run();

} catch (Exception $e) {
    throw new Exception($e->getMessage());
}



