#!/usr/local/bin/php
<?php
require_once 'local.inc';

$debugMode = Logger::DBG_SERV | Logger::DBG_SOCK | Logger::DBG_MESS | Logger::DBG_POOL | Logger::DBG_TASK;
//$debugMode = 0;

$command = '';

if ($_SERVER['argc'] >= 2) {
    $command = $_SERVER['argv'][1];
}

$app = new App(SCRIPT_NAME);

Logger::create($app,LOG_PATH, $debugMode, 'xchain.log', 'error.log', 'php.err');

try {
    // set current node as Client
    $app->setNode(aNode::spawn($app, NodeClassEnum::CLIENT_ID));

    $listenTCPHost = Host::create($app, Host::TRANSPORT_TCP, MY_NODE_ADDR);
    $bindTCPHost = Host::create($app, Host::TRANSPORT_TCP, MY_NODE_ADDR);
    $firstRemoteHost = Host::create($app, Host::TRANSPORT_TCP, FIRST_NODE_ADDR);

    Server::create($app,$listenTCPHost, $bindTCPHost);

    Daemon::create($app, RUN_PATH,  'pid');

    if (!$app->getDaemon()->run($command)) {
        throw new Exception('Cannot daemon start');
    }

//    $startPool = TaskPool::create($app->getServer()->getQueue(), "Start Operations");
//    GetFnodesTask::create($app->getServer(), $startPool, $firstRemoteHost);
//    $startPool->toQueue();

    $app->getServer()->run();
} catch (Exception $e) {
    throw new Exception($e->getMessage());
}
