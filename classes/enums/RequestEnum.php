<?php
/**
 * Enumeration of Requests "request type ID => request type class"
 */

class RequestEnum extends Enum
{
    public const ALIVE_REQ = 1;
    public const ALIVE_RES = 2;

    protected static $list = array(
        0 => '',                                // empty for not using 0 type
        self::ALIVE_REQ => 'AliveReqRequest',
        self::ALIVE_RES => 'AliveResRequest',
    );
}