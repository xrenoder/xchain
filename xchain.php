#!/usr/local/bin/php
<?php
require_once 'local.inc';

$key = "testKey1";
$dbh = dba_open(MAINCHAIN_FILE, "w", DBA_HANDLER);
dba_insert($key, 1234567890, $dbh);
echo var_export( dba_fetch($key, $dbh) . "\n", true);
$test = dba_fetch($key, $dbh) . "\n";
$test += 1234567890;
dba_insert($key, $test, $dbh);
echo var_export( dba_fetch($key, $dbh) . "\n", true);
$test += 1234567890;
dba_replace($key, $test, $dbh);
echo var_export( dba_fetch($key, $dbh) . "\n", true);
dba_optimize ($dbh);
dba_sync($dbh);
dba_close($dbh);

die(var_export(dba_handlers(true), true));

$debugMode = Logger::DBG_SERV | Logger::DBG_SOCK | Logger::DBG_MESS | Logger::DBG_POOL | Logger::DBG_TASK | Logger::DBG_NODE | Logger::DBG_ADDR | Logger::DBG_DBA;
//$debugMode = 0;

$command = '';

if ($_SERVER['argc'] >= 2) {
    $command = $_SERVER['argv'][1];
}

$app = new App(SCRIPT_NAME);

// get logger-object
Logger::create($app,LOG_PATH, $debugMode, 'xchain.log', 'error.log', 'php.err');

try {
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

//    $startPool = TaskPool::create($app->getServer()->getQueue(), "Start Operations");
//    GetFnodesTask::create($app->getServer(), $startPool, $firstRemoteHost);
//    $startPool->toQueue();

    // run server
    $app->getServer()->run();
} catch (Exception $e) {
    throw new Exception($e->getMessage());
}
