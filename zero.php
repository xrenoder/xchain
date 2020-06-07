#!/usr/local/bin/php
<?php
require_once 'local.inc';
require_once 'zero.inc';

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
;
//*/
/*
$debugMode = 0;
*/

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
    $app->setMyNode(aNode::spawn($app, NodeClassEnum::MASTER));

// load node private key
    $app->setMyAddress(Address::createFromWallet($app, MY_ADDRESS, WALLET_PATH));

// create zero-block
    $zero = Zero::create($app);

    $zero->run();

} catch (Exception $e) {
    throw new Exception($e->getMessage() . "\n" . var_export($e->getTraceAsString(), true));
}
