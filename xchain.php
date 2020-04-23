#!/usr/local/bin/php
<?php
require_once 'local.inc';

$debugMode = Logger::DBG_SERV | Logger::DBG_SOCK;
//$debugMode = 0;

$app = new App(SCRIPT_NAME);

Logger::create($app,LOG_PATH, $debugMode, 'xchain.log', 'error.log', 'php.err');

$listenTCPHost = Host::create($app, Host::TCP, MY_IP, MY_PORT);
$bindTCPHost = Host::create($app, Host::TCP, MY_IP, 0);

Server::create($app,$listenTCPHost, $bindTCPHost);

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
