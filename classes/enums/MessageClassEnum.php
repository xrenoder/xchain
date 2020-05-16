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
    protected static $baseClassName = 'aMessage'; /* overrided */

    public const ALIVE_REQ      = 0;
    public const ALIVE_RES      = 1;
    public const BUSY_RES       = 2;
    public const BAD_NODE_RES   = 3;
    public const BAD_TIME_RES   = 4;

    protected static $items = array(
        self::ALIVE_REQ =>      'AliveReqMessage',      // request "Is daemon alive?"
        self::ALIVE_RES =>      'AliveResMessage',      // response "Daemon is alive"
        self::BUSY_RES =>       'BusyResMessage',       // response "Daemon is alive, but busy, cannot accept connections, socket will be closed"
        self::BAD_NODE_RES =>   'BadNodeResMessage',    // response "Daemon is alive, but your node cannot connect to me, socket will be closed"
        self::BAD_TIME_RES =>   'BadTimeResMessage',    // response "Daemon is alive, but node times are unsynchronized, socket will be closed"
    );

}