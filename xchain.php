#!/usr/local/bin/php
<?php
require_once 'local.inc';
require_once 'App.php';

$app = new App();

$app->run();

function signalSoftExit($signo = null)
{
    pcntl_signal($signo, SIG_IGN);
}

function signalHardExit($signo = null)
{
    pcntl_signal($signo, SIG_IGN);
}