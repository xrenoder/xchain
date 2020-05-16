<?php


class MessageFieldClassEnum extends aClassEnum
{
    protected static $baseClassName = 'aMessageField'; /* overrided */

// must be serial from 0
    public const TYPE =    0;
    public const LENGTH =  1;
    public const NODE =    2;
    public const TIME =    3;
    public const ADDR =    4;
    public const DATA =    5;
    public const SIGN =    6;

    public const UNKNOWN_LEN  = 0;
    public const SIMPLE_MAX_LEN  = 1 + 4 + 1 + 4; // type + length + node + time
    public const SIMPLE_ADDR_MAX_LEN  = 1 + 4 + 1 + 4 + 25; // type + length + node + time + addr

    protected static $items = array(
        self::TYPE => 'TypeMessageField',
        self::LENGTH => 'LengthMessageField',
        self::NODE => 'NodeMessageField',
        self::TIME => 'TimeMessageField',
        self::ADDR => 'AddrMessageField',
        self::DATA => 'DataMessageField',
        self::SIGN => 'SignMessageField',
    );

    protected static $data = array(
        self::TYPE =>   FieldFormatEnum::UCHAR,          // always must have fixed non-zero length
        self::LENGTH => FieldFormatEnum::ULONG_BE,
        self::NODE =>   FieldFormatEnum::UCHAR,
        self::TIME =>   FieldFormatEnum::ULONG_BE,
        self::ADDR =>   FieldFormatEnum::ADDR,
        self::DATA =>   FieldFormatEnum::NOPACK_LBE,
        self::SIGN =>   FieldFormatEnum::SIGN_LC,
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