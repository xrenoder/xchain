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
define('DBA_HANDLER', 'qdbm');
define('DBA_EXT', '.qdbm');
define('LOCK_EXT', '.lock');

define('CLASS_EXT', '.php');
define('LOG_EXT', '.log');

define('CLASS_PATH', XCHAIN_PATH . 'classes' . DS);
define('LOG_PATH', XCHAIN_PATH . 'logs' . DS);
define('RUN_PATH', XCHAIN_PATH . 'run' . DS);
define('DATA_PATH', XCHAIN_PATH . 'data' . DS);                    // blockchain data
define('WALLET_PATH', XCHAIN_PATH . 'wallet' . DS);

define('DBA_LOCK_FILE', 'db');           // lock file for database transactions

define('STATES_TABLE', 'states');              // actual summary data states (last prepared block, last known block, rules, etc)
define('NODE_PINGS_TABLE', 'node.pings');      // "address" => "ping" ; used for all nodes; data from tests, not in chain

define('MAINCHAIN_TABLE', 'main.chain');       // "blockNumber" => "blockRawData" ; used for master & torrent nodes

define('TRANSACTIONS_TABLE', 'transactions');  // "transactionId" => '' ; for checking, is transaction ID exists, used for all nodes

define('PUBLIC_KEYS_TABLE', 'public.keys');    // "address" => "publicKey" ; used for all nodes

define('AMOUNTS_TABLE', 'amounts');            // "address" => amount ; used for all nodes
define('DELEGATE_FROM_TABLE', 'delegate.from');// "addressFrom" => amount ; used for all nodes
define('DELEGATE_TO_TABLE', 'delegate.to');    // "addressTo" => amount ; used for all nodes

define('NODE_TYPES_TABLE', 'node.types');      // "address" => nodeType ; used for all nodes
define('NODE_HOSTS_TABLE', 'node.hosts');      // "address" => "host" ; used for all nodes
define('NODE_TRUSTS_TABLE', 'node.trusts');    // "address" => "trust" ; used for all nodes
define('NODE_ONLINE_TABLE', 'node.online');    // "address" => isOnline ; used for all nodes
define('NODE_LOADS_TABLE', 'node.loads');      // "address" => load (accepted connects from top level); used for all nodes
define('NODE_SPACES_TABLE', 'node.spaces');      // "address" => free disk space ; used for all nodes

define('NODES_MAP_TABLE', 'nodes_map');

require_once 'init.inc';

