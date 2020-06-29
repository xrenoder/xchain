<?php


class MessageDataClassEnum extends aClassEnum
{
    protected static $baseClassName = 'aMessageData'; /* overrided */

    public const TRANSACTION =       0;

    protected static $items = array(
        self::TRANSACTION =>      'TransactionMessageData',
    );
}