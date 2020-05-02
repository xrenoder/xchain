<?php
/**
 * Enumeration of messages
 * items:   "message type ID" => "message class"
 * data:    "message type ID" => array(
 *              "maxLength" => maximal length of message
 *          )
 */

class MessageClassEnum extends aClassEnum
{
    protected static $baseClassName = 'aMessage';

    public const DATA_MAX_LEN  = 'maxLength';

    public const ALIVE_REQ      = 1;
    public const ALIVE_RES      = 2;
    public const BUSY_RES       = 3;
    public const BAD_NODE_RES   = 4;
    public const NODES_REQ      = 5;

    protected static $items = array(
        0 => '',                                        // cannot use ZERO-id
        self::ALIVE_REQ =>      'AliveReqMessage',      // request "Is daemon alive?"
        self::ALIVE_RES =>      'AliveResMessage',      // response "Daemon is alive"
        self::BUSY_RES =>       'BusyResMessage',       // response "Daemon is alive, but busy, cannot accept connections, socket will be closed"
        self::BAD_NODE_RES =>   'BadNodeResMessage',    // response "Daemon is alive, but your node cannot connect to me, socket will be closed"
    );

    protected static $data = array(
        0 => array(),
        self::ALIVE_REQ =>      array(self::DATA_MAX_LEN => MessFldEnum::SIMPLE_MAX_LEN),
        self::ALIVE_RES =>      array(self::DATA_MAX_LEN => MessFldEnum::SIMPLE_MAX_LEN),
        self::BUSY_RES =>       array(self::DATA_MAX_LEN => MessFldEnum::SIMPLE_MAX_LEN),
        self::BAD_NODE_RES =>   array(self::DATA_MAX_LEN => MessFldEnum::SIMPLE_MAX_LEN),
    );

    /**
     * @param int $id
     * @return int
     * @throws Exception
     */
    public static function getMaxLen(int $id) : int
    {
        return static::$data[$id][self::DATA_MAX_LEN];
    }
}