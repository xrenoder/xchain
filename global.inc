<?php

define('VERSION', '000001.2020.04.18.01');

if (!defined('XCHAIN_DIR')) {
    throw new Exception('XCHAIN_DIR should be defined');
}

define('CLASS_EXT', '.php');
define('LOG_EXT', '.log');

define('CLASS_PATH', XCHAIN_PATH . 'classes' . DS);
define('LOG_PATH', XCHAIN_PATH . 'logs' . DS);
define('RUN_PATH', XCHAIN_PATH . 'run' . DS);
define('DATA_PATH', XCHAIN_PATH . 'data' . DS);

define('RESERVE_SOCKETS', 128);
define('MAX_SOCKETS', 640);     // 512 + 128 reserve sockets

define('NODES_MAP_FILE', DATA_PATH . 'nodes_map');

require_once 'init.inc';

