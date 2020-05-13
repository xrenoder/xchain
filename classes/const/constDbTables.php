<?php


interface constDbTables
{
    public const SUMMARY_TABLE = 'summary';               // actual summary blockchain data (last prepared block, last known block, rules, etc)
    protected const NODE_PINGS_TABLE = 'node.pings';      // "address" => "ping" ; used for all nodes; data from tests, not in chain

    protected const MAINCHAIN_TABLE = 'main.chain';       // "blockNumber" => "blockRawData" ; used for master & torrent nodes

    protected const TRANSACTIONS_TABLE = 'transactions';  // "transactionId" => '' ; for checking, is transaction ID exists, used for all nodes

    protected const PUBLIC_KEYS_TABLE = 'public.keys';    // "address" => "publicKey" ; used for all nodes

    protected const AMOUNTS_TABLE = 'amounts';            // "address" => amount ; used for all nodes
    protected const DELEGATE_FROM_TABLE = 'delegate.from';// "addressFrom" => amount ; used for all nodes
    protected const DELEGATE_TO_TABLE = 'delegate.to';    // "addressTo" => amount ; used for all nodes

    protected const NODE_TYPES_TABLE = 'node.types';      // "address" => nodeType ; used for all nodes
    protected const NODE_HOSTS_TABLE = 'node.hosts';      // "address" => "host" ; used for all nodes
    protected const NODE_TRUSTS_TABLE = 'node.trusts';    // "address" => "trust" ; used for all nodes
    protected const NODE_ONLINE_TABLE = 'node.online';    // "address" => isOnline ; used for all nodes
    protected const NODE_LOADS_TABLE = 'node.loads';      // "address" => load (accepted connects from top level); used for all nodes
    protected const NODE_SPACES_TABLE = 'node.spaces';    // "address" => free disk space ; used for all nodes

    protected const NODES_MAP_TABLE = 'nodes_map';
}