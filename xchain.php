#!/usr/local/bin/php
<?php
require_once 'local.inc';

use parallel\{Channel,Runtime,Events,Events\Event,Events\Event\Type};

///*
$debugMode =
      Logger::DBG_LOCATOR
    | Logger::DBG_DAEMON
    | Logger::DBG_SERVER
    | Logger::DBG_SOCKET
    | Logger::DBG_MESSAGE
    | Logger::DBG_POOL
    | Logger::DBG_TASK
    | Logger::DBG_NODE
    | Logger::DBG_ADDRESS
    | Logger::DBG_DBA
    | Logger::DBG_MSG_FLD
    | Logger::DBG_DB_FLD
    | Logger::DBG_DB_FROW
    | Logger::DBG_DB_MROW
    | Logger::DBG_DB_RSET
    | Logger::DBG_TRANSACT
    | Logger::DBG_TRANS_FLD
    | Logger::DBG_BLOCK
    | Logger::DBG_TRANSACT
    | Logger::DBG_TRANS_DATA_FLD
    | Logger::DBG_TRANS_DATA
    | Logger::DBG_BLOCK_SECTION
    | Logger::DBG_BLOCK_FLD
;
//*/
/*
$debugMode = 0;
*/

$command = '';

if ($_SERVER['argc'] >= 2) {
    $command = $_SERVER['argv'][1];
}

$app = new App(SCRIPT_NAME);

// get logger-object
Logger::create(
    $app,
    $debugMode,
    LOG_PATH,
    LOG_EXT,
    LOG_FILE,
    SCRIPT_ERROR_LOG_FILE,
    PHP_ERROR_LOG_FILE
);

try {
//    $app->setZeroAddress(Address::createZero($app));

// set DBA
    DBA::create($app, DBA_HANDLER, DATA_PATH, DBA_EXT, DBA_LOCK_FILE, LOCK_EXT);

// check DB integrity
    if ($app->getDba()->integrity() === true) {
        $app->log("DB integrity test passed");
    } else {
        $app->log("DB integrity test failed");
        $app->getDba()->removeDbTables();
        $app->log("All DB tables was removed, application stopped for restarting");
        exit(0);
    }

// set current node as Client (always, before full syncronization)
    $app->setMyNodeType(NodeEnum::CLIENT);

// get server-object
    $listenTCPHost = Host::create($app, Host::TRANSPORT_TCP, MY_NODE_HOST);
    $bindTCPHost = Host::create($app, Host::TRANSPORT_TCP, MY_NODE_HOST);
    $firstRemoteHost = Host::create($app, Host::TRANSPORT_TCP, FIRST_NODE_HOST);

    Server::create($app,$listenTCPHost, $bindTCPHost);

// load node private key
    $app->setMyAddress(Address::createFromWallet($app, MY_ADDRESS, WALLET_PATH));

// get daemon-object
    Daemon::create($app, RUN_PATH,  'pid');

// start daemon
    if (!$app->getDaemon()->run($command)) {
        throw new Exception('Cannot daemon start');
    }

//    $startPool = TaskPool::create($app->getServer()->getQueue(), "Start Operations");
//    GetFnodesTask::create($app->getServer(), $startPool, $firstRemoteHost);
//    $startPool->toQueue();

// start server
    $app->getServer()->run();
} catch (Exception $e) {
// hard close all workers
    $threads = $app->getAllThreads();

    foreach($threads as $threadId => $thread) {
        $channel = $app->getChannelFromParent($threadId);
        CommandToWorker::send($channel, CommandToWorker::MUST_DIE_HARD);
        $app->log("Command to hard finish thread $threadId sended");
    }

    sleep(1);

    throw new Exception($e->getMessage() . "\n" . var_export($e->getTraceAsString(), true));
}
