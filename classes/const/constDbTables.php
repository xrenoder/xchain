<?php


interface constDbTables
{
    public const SUMMARY_TABLE = 'summary';               // actual fixed blockchain data (last prepared block, last known block, rules, etc); used for all non-client nodes

    public const ADDR_PUBKEYS_TABLE = 'addr.pubkeys';    // "address" => "publicKey"; used for all non-client nodes
    public const ADDR_NODES_TABLE = 'addr.nodes';    // "address" => "nodeType"; used for all non-client nodes

    public const NODE_PINGS_TABLE = 'node.pings';      // "address" => "ping" ; used for all nodes; data from tests, not in chain

    public const BLOCKS_TABLE = 'blocks';               // "blockNumber" => "blockRawData" ; used for master & torrent nodes

    public const TRANSACTIONS_TABLE = 'transactions';  // "transactionId" => nonce ; for checking, is transaction ID exists, used for all nodes

    public const AMOUNTS_TABLE = 'amounts';            // "address" => amount ; used for all nodes
    public const DELEGATE_FROM_TABLE = 'delegate.from';// "addressFrom" => amount ; used for all nodes
    public const DELEGATE_TO_TABLE = 'delegate.to';    // "addressTo" => amount ; used for all nodes

    public const NODE_TYPES_TABLE = 'node.types';      // "address" => nodeType ; used for all nodes
    public const NODE_HOSTS_TABLE = 'node.hosts';      // "address" => "host" ; used for all nodes
    public const NODE_TRUSTS_TABLE = 'node.trusts';    // "address" => "trust" ; used for all nodes
    public const NODE_ONLINE_TABLE = 'node.online';    // "address" => isOnline ; used for all nodes
    public const NODE_LOADS_TABLE = 'node.loads';      // "address" => load (accepted connects from top level); used for all nodes
    public const NODE_SPACES_TABLE = 'node.spaces';    // "address" => free disk space ; used for all nodes

    public const NODES_MAP_TABLE = 'nodes_map';
}