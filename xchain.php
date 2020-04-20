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
    $GLOBALS['softFinish'] = true;
}

function signalHardExit($signo, $siginfo = null)
{
    $GLOBALS['hardFinish'] = true;
}