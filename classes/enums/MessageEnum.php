<?php
/**
 * Enumeration of Messages "message type ID => message type class"
 */

class MessageEnum extends ClassEnum
{
//    protected static $baseClassName = 'Message';

    public const ALIVE_REQ = 1;
    public const ALIVE_RES = 2;
    public const LEVEL_MAP_REQ = 3;

    protected static $items = array(
        0 => '',                                // empty for not using 0 type
        self::ALIVE_REQ => 'AliveReqMessage',
        self::ALIVE_RES => 'AliveResMessage',
    );
}