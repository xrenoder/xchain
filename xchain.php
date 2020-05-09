#!/usr/local/bin/php
<?php
require_once 'local.inc';

$debugMode
    = Logger::DBG_SERV
    | Logger::DBG_SOCK
    | Logger::DBG_MESS
    | Logger::DBG_POOL
    | Logger::DBG_TASK
    | Logger::DBG_NODE
    | Logger::DBG_ADDR
    | Logger::DBG_DBA
    | Logger::DBG_MSG_FLD;

//$debugMode = 0;

$command = '';

if ($_SERVER['argc'] >= 2) {
    $command = $_SERVER['argv'][1];
}

$app = new App(SCRIPT_NAME);

// get logger-object
Logger::create($app,LOG_PATH, $debugMode, 'xchain.log', 'error.log', 'php.err');

try {
    // set DBA
    DBA::create($app, DBA_HANDLER, DATA_PATH, DBA_EXT, DBA_LOCK_FILE, LOCK_EXT);

    // set current node as Client (always, before full syncronization)
    $app->setMyNode(aNode::spawn($app, NodeClassEnum::CLIENT_ID));

    // get server-object
    $listenTCPHost = Host::create($app, Host::TRANSPORT_TCP, MY_NODE_HOST);
    $bindTCPHost = Host::create($app, Host::TRANSPORT_TCP, MY_NODE_HOST);
    $firstRemoteHost = Host::create($app, Host::TRANSPORT_TCP, FIRST_NODE_HOST);

    Server::create($app,$listenTCPHost, $bindTCPHost);

    // get daemon-object
    Daemon::create($app, RUN_PATH,  'pid');

    // run daemon
    if (!$app->getDaemon()->run($command)) {
        throw new Exception('Cannot daemon start');
    }

    // load node private key
    $app->setMyAddr(Address::createFromWallet($app, MY_ADDRESS, WALLET_PATH));

    // load chain state data
    ChainState::create($app, STATES_TABLE);

//    $startPool = TaskPool::create($app->getServer()->getQueue(), "Start Operations");
//    GetFnodesTask::create($app->getServer(), $startPool, $firstRemoteHost);
//    $startPool->toQueue();

    // run server
    $app->getServer()->run();
} catch (Exception $e) {
    throw new Exception($e->getMessage());
}
