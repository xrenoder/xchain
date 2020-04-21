#!/usr/local/bin/php
<?php
require_once 'local.inc';
require_once 'App.php';

$GLOBALS['softFinish'] = false;
$GLOBALS['hardFinish'] = false;

$app = new App();

$app->run();

function signalSoftExit($signo = null)
{
    pcntl_signal($signo, SIG_IGN);
    $GLOBALS['softFinish'] = true;
}

function signalHardExit($signo = null)
{
    pcntl_signal($signo, SIG_IGN);
    $GLOBALS['hardFinish'] = true;
}