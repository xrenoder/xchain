#!/usr/local/bin/php
<?php
require_once 'local.inc';
require_once 'App.php';

$GLOBALS['softFinish'] = false;
$GLOBALS['hardFinish'] = false;

$app = new App();

$app->run();

function signalSoftExit($signo, $siginfo = null)
{
    throw new Exception('SIGHUP');
    $GLOBALS['softFinish'] = true;
}

function signalHardExit($signo, $siginfo = null)
{
    throw new Exception('SIGTERM');
    $GLOBALS['hardFinish'] = true;
}