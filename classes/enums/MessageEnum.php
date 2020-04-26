<?php
/**
 * Enumeration of messages: "message type ID => message class"
 */

class MessageEnum extends aClassEnum
{
    protected static $baseClassName = 'aMessage';

    public const ALIVE_REQ = 1;
    public const ALIVE_RES = 2;
    public const NODES_REQ = 3;

    protected static $items = array(
        0 => '',                                // empty for not using 0 type
        self::ALIVE_REQ => 'AliveReqMessage',
        self::ALIVE_RES => 'AliveResMessage',
    );
}