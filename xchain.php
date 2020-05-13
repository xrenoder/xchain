#!/usr/local/bin/php
<?php
require_once 'local.inc';

///*
$debugMode
    = Logger::DBG_SERV
    | Logger::DBG_SOCK
    | Logger::DBG_MESS
    | Logger::DBG_POOL
    | Logger::DBG_TASK
    | Logger::DBG_NODE
    | Logger::DBG_ADDR
    | Logger::DBG_DBA
    | Logger::DBG_MSG_FLD
    | Logger::DBG_ROW
    | Logger::DBG_ROW_SET;
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

    // load node private key
    $app->setMyAddr(Address::createFromWallet($app, MY_ADDRESS, WALLET_PATH));

//
    $text1 = "ldfjdljgal;saldgasldkjsagdlaskdgj;asldgkjas;ldkjgas;dgjas;dgkjas;dlkgjas;dlgkjas;dgjk;saldgkjsa;gdkj";
    $text2 = "ldfjdljgal;saldgasldkjsagdlaskdgj;asldgkjas;ldkjgas;dgjas;dgkjas;dlkgjas;dlgkjas;dgjk;saldgkjsa;gdkjoetquoqweotpqoieutpqoupopzpovicxp";
    $sign1 = $app->getMyAddr()->signBin($text1);
    $sign2 = $app->getMyAddr()->signBin($text2);
    $sign1hex = bin2hex($sign1);
    $sign2hex = bin2hex($sign2);
    $ver1 = $app->getMyAddr()->verifyBin($sign1, $text1);
    $ver2 = $app->getMyAddr()->verifyBin($sign2, $text2);

    die("$sign1hex\n\n$sign2hex\n\n" . strlen($sign1hex) . "\n" . strlen($sign1) . " bytes\n" . strlen($sign2hex) . "\n" . strlen($sign2) . " bytes\n$ver1\n$ver2\n");

    // get daemon-object
    Daemon::create($app, RUN_PATH,  'pid');

    // run daemon
    if (!$app->getDaemon()->run($command)) {
        throw new Exception('Cannot daemon start');
    }

    // load chain state data
    SummaryDataSet::create($app);

//    $startPool = TaskPool::create($app->getServer()->getQueue(), "Start Operations");
//    GetFnodesTask::create($app->getServer(), $startPool, $firstRemoteHost);
//    $startPool->toQueue();

    // run server
    $app->getServer()->run();
} catch (Exception $e) {
    throw new Exception($e->getMessage());
}
