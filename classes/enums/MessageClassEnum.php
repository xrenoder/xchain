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

    public const UNLIMIT_LEN  = 0;
    public const SIMPLE_LEN  = icMessage::FLD_LENGTH_LEN + icMessage::FLD_TYPE_LEN + icMessage::FLD_NODE_LEN;

    public const ALIVE_REQ  = 1;
    public const ALIVE_RES  = 2;
    public const BUSY_RES   = 3;
    public const NODES_REQ  = 4;

    protected static $items = array(
        0 => '',                                    // cannot use ZERO-id
        self::ALIVE_REQ =>  'AliveReqMessage',      // request "Is daemon alive?"
        self::ALIVE_RES =>  'AliveResMessage',      // response "Daemon is alive"
        self::BUSY_RES =>   'BusyResMessage',       // response "Daemon is alive, but busy, cannot accept connections, socket will be closed"
    );

    protected static $data = array(
        0 => array(),
        self::ALIVE_REQ =>  array(self::DATA_MAX_LEN => self::SIMPLE_LEN),
        self::ALIVE_RES =>  array(self::DATA_MAX_LEN => self::SIMPLE_LEN),
        self::BUSY_RES =>  array(self::DATA_MAX_LEN => self::SIMPLE_LEN),
    );

    /**
     * @param int $id
     * @return int
     * @throws Exception
     */
    public static function getMaxMessageLen(int $id) : int
    {
        if (!static::isSetData($id)) return static::UNLIMIT_LEN;
        $data = static::getData($id);
        return $data[self::DATA_MAX_LEN] ?? static::UNLIMIT_LEN;
    }
}