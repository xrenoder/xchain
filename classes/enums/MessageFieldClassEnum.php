<?php


class MessageFieldClassEnum extends aClassEnum
{
    protected static $baseClassName = 'aMessageField'; /* overrided */

// must be serial from 0
    public const MESS_FLD_TYPE = 0;
    public const MESS_FLD_LENGTH = 1;
    public const MESS_FLD_NODE = 2;
    public const MESS_FLD_TIME = 3;

    public const UNLIMIT_LEN  = 0;
    public const SIMPLE_MAX_LEN  = 1 + 4 + 1 + 4; // type + length + node + time

    protected static $items = array(
        self::MESS_FLD_TYPE => 'TypeMessageField',
        self::MESS_FLD_LENGTH => 'LengthMessageField',
        self::MESS_FLD_NODE => 'NodeMessageField',
        self::MESS_FLD_TIME => 'TimeMessageField',
    );

    protected static $data = array(
        self::MESS_FLD_TYPE => FieldFormatEnum::UCHAR,          // always must have fixed non-zero length
        self::MESS_FLD_LENGTH => FieldFormatEnum::ULONG_BE,
        self::MESS_FLD_NODE => FieldFormatEnum::UCHAR,
        self::MESS_FLD_TIME => FieldFormatEnum::ULONG_BE,
    );

    public static function getFormat(int $fieldId) : string
    {
        return static::$data[$fieldId];
    }

    public static function getLength(int $fieldId) : int
    {
        return FieldFormatEnum::getLength(static::$data[$fieldId]);
    }
}