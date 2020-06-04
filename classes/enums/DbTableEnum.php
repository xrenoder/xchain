<?php


class DbTableEnum extends aEnum
{
    public const INTEGRITY = 'integrity';    // data for DB-integrity checking

    public const SUMMARY = 'summary';               // actual fixed blockchain data (last prepared block, last known block, rules, etc); used for all non-client nodes

    public const ADDR_PUBKEYS = 'addr.pubkeys';    // "address" => "publicKey"; used for all non-client nodes
    public const ADDR_NODES = 'addr.nodes';    // "address" => "nodeType"; used for all non-client nodes

    public const NODE_PINGS = 'node.pings';      // "address" => "ping" ; used for all nodes; data from tests, not in chain

    public const BLOCKS = 'blocks';               // "blockNumber" => "blockRawData" ; used for master & torrent nodes

    public const TRANSACTIONS = 'transactions';  // "transactionId" => nonce ; for checking, is transaction ID exists, used for all nodes

    public const AMOUNTS = 'amounts';            // "address" => amount ; used for all nodes
    public const DELEGATE_FROM = 'delegate.from';// "addressFrom" => amount ; used for all nodes
    public const DELEGATE_TO = 'delegate.to';    // "addressTo" => amount ; used for all nodes

    public const NODE_TYPES = 'node.types';      // "address" => nodeType ; used for all nodes
    public const NODE_HOSTS = 'node.hosts';      // "address" => "host" ; used for all nodes
    public const NODE_TRUSTS = 'node.trusts';    // "address" => "trust" ; used for all nodes
    public const NODE_ONLINE = 'node.online';    // "address" => isOnline ; used for all nodes
    public const NODE_LOADS = 'node.loads';      // "address" => load (accepted connects from top level); used for all nodes
    public const NODE_SPACES = 'node.spaces';    // "address" => free disk space ; used for all nodes

    public const NODES_MAP = 'nodes_map';

    protected static $items = array(
        self::SUMMARY => self::SUMMARY,

        self::ADDR_PUBKEYS => self::ADDR_PUBKEYS,
        self::ADDR_NODES => self::ADDR_NODES,
/*
        self::NODE_PINGS_TABLE => 'node.pings',

        self::BLOCKS_TABLE => 'blocks',

        self::TRANSACTIONS_TABLE => 'transactions',

        self::AMOUNTS_TABLE => 'amounts',
        self::DELEGATE_FROM_TABLE => 'delegate.from',
        self::DELEGATE_TO_TABLE => 'delegate.to',

        self::NODE_TYPES_TABLE => 'node.types',
        self::NODE_HOSTS_TABLE => 'node.hosts',
        self::NODE_TRUSTS_TABLE => 'node.trusts',
        self::NODE_ONLINE_TABLE => 'node.online',
        self::NODE_LOADS_TABLE => 'node.loads',
        self::NODE_SPACES_TABLE => 'node.spaces',

        self::NODES_MAP_TABLE => 'nodes_map',
*/
    );
}