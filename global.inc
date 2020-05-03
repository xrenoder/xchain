<?php
define('VERSION', '000001.2020.04.18.01');

if (!defined('XCHAIN_PATH')) {
    throw new Exception('XCHAIN_PATH should be defined');
}

define('RESERVE_SOCKETS', 128);
define('MAX_SOCKETS', 640);     // 512 + 128 reserve sockets

define('CLASS_EXT', '.php');
define('LOG_EXT', '.log');

define('CLASS_PATH', XCHAIN_PATH . 'classes' . DS);
define('LOG_PATH', XCHAIN_PATH . 'logs' . DS);
define('RUN_PATH', XCHAIN_PATH . 'run' . DS);
define('DATA_PATH', XCHAIN_PATH . 'data' . DS);
define('BLOCKCHAIN_PATH', XCHAIN_PATH . 'blockchain' . DS);
define('WALLET_PATH', XCHAIN_PATH . 'wallet' . DS);

define('NODES_MAP_FILE', DATA_PATH . 'nodes_map');

require_once 'init.inc';

