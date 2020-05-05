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
//define('DBA_HANDLER', 'qdbm');
//define('DBA_EXT', '.qdbm');

define('DBA_HANDLER', 'cdb_make');
define('DBA_EXT', '.cdbmk');



define('CLASS_EXT', '.php');
define('LOG_EXT', '.log');

define('CLASS_PATH', XCHAIN_PATH . 'classes' . DS);
define('LOG_PATH', XCHAIN_PATH . 'logs' . DS);
define('RUN_PATH', XCHAIN_PATH . 'run' . DS);
define('DATA_PATH', XCHAIN_PATH . 'data' . DS);                 // blockchain organized data
define('WALLET_PATH', XCHAIN_PATH . 'wallet' . DS);

define('COMMON_FILE', DATA_PATH . 'common' . DBA_EXT);              // actual summary data states (last prepared block, last known block, etc)
define('NODE_PINGS_FILE', DATA_PATH . 'node.pings' . DBA_EXT);      // "address" => "ping" ; used for all nodes; data from tests, not in chain

define('MAINCHAIN_FILE', DATA_PATH . 'main.chain' . DBA_EXT);       // "blockNumber" => "blockRawData" ; used for master & torrent nodes

define('PUBLIC_KEYS_FILE', DATA_PATH . 'public.keys' . DBA_EXT);    // "address" => "publicKey" ; used for all nodes

define('AMOUNTS_FILE', DATA_PATH . 'amounts' . DBA_EXT);            // "address" => amount ; used for all nodes
define('DELEGATE_FROM_FILE', DATA_PATH . 'delegate.from' . DBA_EXT);// "addressFrom" => amount ; used for all nodes
define('DELEGATE_TO_FILE', DATA_PATH . 'delegate.to' . DBA_EXT);    // "addressTo" => amount ; used for all nodes


define('NODE_TYPES_FILE', DATA_PATH . 'node.types' . DBA_EXT);      // "address" => nodeType ; used for all nodes
define('NODE_HOSTS_FILE', DATA_PATH . 'node.hosts' . DBA_EXT);      // "address" => "host" ; used for all nodes
define('NODE_TRUSTS_FILE', DATA_PATH . 'node.trusts' . DBA_EXT);    // "address" => "trust" ; used for all nodes
define('NODE_ONLINE_FILE', DATA_PATH . 'node.online' . DBA_EXT);    // "address" => isOnline ; used for all nodes
define('NODE_LOADS_FILE', DATA_PATH . 'node.loads' . DBA_EXT);      // "address" => load ; used for all nodes




define('NODES_MAP_FILE', DATA_PATH . 'nodes_map');

require_once 'init.inc';

