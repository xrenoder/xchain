<?php


class MessageDataFieldClassEnum extends aFieldClassEnum
{
    protected static $baseClassName = 'aMessageDataField'; /* overrided */

    public const TRANSACTION =       0;

    protected static $items = array(
        self::TRANSACTION =>     'TransactionMessageDataField',
    );

    protected static $data = array(
        self::TRANSACTION =>    FieldFormatClassEnum::ASIS,
    );
}