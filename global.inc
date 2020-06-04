<?php
// TODO реализовать распознавание совместимости версий
define('TOP_VERSION', 1); // max 255
define('MIN_VERSION', 0); // max 255
define('SUB_VERSION', 1); // max 255
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

define('CLASS_EXT', '.php');

define('LOG_FILE', 'xchain');
define('SCRIPT_ERROR_LOG_FILE', 'error');
define('PHP_ERROR_LOG_FILE', 'php.err');
define('LOG_EXT', '.log');

define('DBA_HANDLER', 'qdbm');
define('DBA_EXT', '.qdbm');
define('DBA_LOCK_FILE', 'db');                          // lock file for database transactions
define('LOCK_EXT', '.lock');

define('CLASS_PATH', XCHAIN_PATH . 'classes' . DS);
define('LOG_PATH', XCHAIN_PATH . 'logs' . DS);
define('RUN_PATH', XCHAIN_PATH . 'run' . DS);
define('DATA_PATH', XCHAIN_PATH . 'data' . DS);
define('WALLET_PATH', XCHAIN_PATH . 'wallet' . DS);

define('ALIVE_TIMEOUT', 10);
define('KILL_TIMEOUT', 10);
define('SELECT_TIMEOUT_SEC', 0);
define('SELECT_TIMEOUT_USEC', 50000);
define('CONNECT_TIMEOUT', 5);
define('RW_TIMEOUT', 1);
define('GARBAGE_TIMEOUT', 60);

define('DBG_ONEBYTE_SEND_USLEEP', 50000);  // 50000 for byte-to-byte messages sending, 0 for normal mode

require_once 'init.inc';

