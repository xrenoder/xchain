<?php

define('VERSION', '000001.2020.04.18.01');

if (!defined('XCHAIN_DIR')) {
    throw new Exception('XCHAIN_DIR should be defined');
}

define('CLASS_EXT', '.php');
define('LOG_EXT', '.log');

define('CLASS_PATH', XCHAIN_DIR . 'classes' . DS);
define('LOG_PATH', XCHAIN_DIR . 'logs' . DS);
define('RUN_PATH', XCHAIN_DIR . 'run' . DS);


require_once 'init.inc';

