<?php


class DbTableEnum extends aEnum
{
    public const INTEGRITY = 'integrity';    // data for DB-integrity checking

    public const SUMMARY = 'summary';             // actual fixed blockchain data (rules, etc); used for all non-client nodes

    public const CHAINS = 'chains';               // "chainId" => ["lastPreparedBlockId", "lastPreparedBlockTime", "lastPreparedBlockSignature", "lastKnownBlockId"]  ; used for master & torrent nodes
    public const BLOCKS = 'blocks';               // "blockNumber" => "blockRawData" ; used for master & torrent nodes
    public const NEW_AUTHOR_TRANSACTIONS = 'new.author.transactions';               // "hash" => "raw transaction" ; used for master & torrent nodes
    public const NEW_SIGNER_TRANSACTIONS = 'new.signer.transactions';               // "hash" => "raw transaction" ; used for master & torrent nodes
    public const NEW_PUBlIC_KEYS = 'new.public.keys';                               // "address" => "public key" ; used for master & torrent nodes
    public const UNIQUE_TRANSACTIONS = 'unique.transactions';                       // "hash" => "last nonce" ; used for master & torrent nodes

    public const PUBLIC_KEYS = 'public.keys';    // "address" => "publicKey"; used for all non-client nodes
    public const NODE_TYPES = 'node.types';    // "address" => "nodeType"; used for all non-client nodes

    public const NODE_PINGS = 'node.pings';      // "address" => "ping" ; used for all nodes; data from tests, not in chain

    public const TRANSACTIONS = 'transactions';  // "transactionId" => nonce ; for checking, is transaction ID exists, used for all nodes

    public const AMOUNTS = 'amounts';            // "address" => amount ; used for all nodes
    public const DELEGATE_FROM = 'delegate.from';// "addressFrom" => amount ; used for all nodes
    public const DELEGATE_TO = 'delegate.to';    // "addressTo" => amount ; used for all nodes

    public const NODE_HOSTS = 'node.hosts';      // "address" => "host" ; used for all nodes
    public const NODE_TRUSTS = 'node.trusts';    // "address" => "trust" ; used for all nodes
    public const NODE_ONLINE = 'node.online';    // "address" => isOnline ; used for all nodes
    public const NODE_LOADS = 'node.loads';      // "address" => load (accepted connects from top level); used for all nodes
    public const NODE_SPACES = 'node.spaces';    // "address" => free disk space ; used for all nodes

    public const NODES_MAP = 'nodes_map';

    protected static $items = array(
        self::INTEGRITY => self::INTEGRITY,
        self::SUMMARY => self::SUMMARY,
        self::CHAINS => self::CHAINS,
        self::BLOCKS => self::BLOCKS,
        self::NEW_AUTHOR_TRANSACTIONS => self::NEW_AUTHOR_TRANSACTIONS,
        self::NEW_SIGNER_TRANSACTIONS => self::NEW_SIGNER_TRANSACTIONS,
        self::UNIQUE_TRANSACTIONS => self::UNIQUE_TRANSACTIONS,

        self::PUBLIC_KEYS => self::PUBLIC_KEYS,
        self::NODE_TYPES => self::NODE_TYPES,

/*
        self::NODE_PINGS_TABLE => 'node.pings',
        self::TRANSACTIONS_TABLE => 'transactions',

        self::AMOUNTS_TABLE => 'amounts',
        self::DELEGATE_FROM_TABLE => 'delegate.from',
        self::DELEGATE_TO_TABLE => 'delegate.to',

        self::NODE_HOSTS_TABLE => 'node.hosts',
        self::NODE_TRUSTS_TABLE => 'node.trusts',
        self::NODE_ONLINE_TABLE => 'node.online',
        self::NODE_LOADS_TABLE => 'node.loads',
        self::NODE_SPACES_TABLE => 'node.spaces',

        self::NODES_MAP_TABLE => 'nodes_map',
*/
    );
}