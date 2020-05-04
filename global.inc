<?php
// TODO реализовать распознавание совместимости версий
define('TOP_VERSION', 1);
define('MIN_VERSION', 0);
define('SUB_VERSION', 1);
define('DATE_VERSION', '2020.04.18.01');

if (!defined('XCHAIN_PATH')) {
    throw new Exception('XCHAIN_PATH should be defined in file local.inc');
}

if (!defined('MY_ADDRESS')) {
    throw new Exception('MY_ADDRESS should be defined in file local.inc');
}

if (MY_ADDRESS === '') {
    die("\nTo create XChain-address run\n/usr/local/bin/php ./create_addr.php\nand copy created address '0x...' to local.inc:\ndefine('MY_ADDRESS', '0x...');");
}

define('RESERVE_SOCKETS', 128);
define('MAX_SOCKETS', 640);     // 512 + 128 reserve sockets
define('DBA_HANDLER', 'lmdb');
define('DBA_EXT', '.lmdb');

define('CLASS_EXT', '.php');
define('LOG_EXT', '.log');

define('CLASS_PATH', XCHAIN_PATH . 'classes' . DS);
define('LOG_PATH', XCHAIN_PATH . 'logs' . DS);
define('RUN_PATH', XCHAIN_PATH . 'run' . DS);
define('DATA_PATH', XCHAIN_PATH . 'data' . DS);                 // blockchain organized data
define('WALLET_PATH', XCHAIN_PATH . 'wallet' . DS);

define('MAINCHAIN_FILE', DATA_PATH . 'main.chain' . DBA_EXT);              // "blockNumber" => "blockRawData" ; need for master & torrent nodes


define('NODES_MAP_FILE', DATA_PATH . 'nodes_map');

require_once 'init.inc';

