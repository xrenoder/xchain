<?php
/**
 * Enumeration of messages: "message type ID => message class"
 */

class MessageClassEnum extends aClassEnum
{
    protected static $baseClassName = 'aMessage';

    public const DATA_MAX_LEN  = 'maxLength';

    public const UNLIMIT_LEN  = 0;
    public const SIMPLE_LEN  = icMessage::FLD_LENGTH_LEN + icMessage::FLD_TYPE_LEN;

    public const ALIVE_REQ  = 255;
    public const ALIVE_RES  = 1;
    public const BUSY_RES   = 2;
    public const NODES_REQ  = 3;

    protected static $items = array(
        self::ALIVE_REQ =>  'AliveReqMessage',      // request "Is daemon alive?"
        self::ALIVE_RES =>  'AliveResMessage',      // response "Daemon is alive"
        self::BUSY_RES =>   'BusyResMessage',       // response "Daemon is alive, but busy, cannot accept connections, socket will be closed"
    );

    protected static $data = array(
        self::ALIVE_REQ =>  array(self::DATA_MAX_LEN => self::SIMPLE_LEN),
        self::ALIVE_RES =>  array(self::DATA_MAX_LEN => self::SIMPLE_LEN),
        self::BUSY_RES =>  array(self::DATA_MAX_LEN => self::SIMPLE_LEN),
    );
}