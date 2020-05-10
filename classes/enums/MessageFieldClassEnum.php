<?php


class MessageFieldClassEnum extends aClassEnum
{
    protected static $baseClassName = 'aMessageField'; /* overrided */

// must be serial from 0
    public const MESS_FLD_TYPE = 0;
    public const MESS_FLD_LENGTH = 1;
    public const MESS_FLD_NODE = 2;

    public const UNLIMIT_LEN  = 0;
    public const SIMPLE_MAX_LEN  = 1 + 4 + 1;

    protected static $items = array(
        self::MESS_FLD_TYPE => 'TypeMessageField',
        self::MESS_FLD_LENGTH => 'LengthMessageField',
        self::MESS_FLD_NODE => 'NodeMessageField',
    );

    protected static $data = array(
        self::MESS_FLD_TYPE => FieldFormatEnum::UCHAR,
        self::MESS_FLD_LENGTH => FieldFormatEnum::ULONG_BE,
        self::MESS_FLD_NODE => FieldFormatEnum::UCHAR,
    );

    public static function prepareField(int $fieldId, string $str) : string
    {
        $format = static::getFormat($fieldId);
        $offset = static::getOffset($fieldId);
        $length = static::getLength($fieldId);
        $tmp = unpack($format, substr($str, $offset, $length));
        return $tmp[1];
    }

    public static function getFormat(int $fieldId) : string
    {
        return static::$data[$fieldId];
    }

    public static function getLength(int $fieldId) : int
    {
        return FieldFormatEnum::getLength(static::$data[$fieldId]);
    }

    public static function getOffset(int $fieldId) : int
    {
        $offset = 0;

        for ($i = 0; $i < $fieldId; $i++) {
            $offset += static::getLength($i);
        }

        return $offset;
    }

    public static function getLenLength() : int
    {
        return FieldFormatEnum::getLength(static::$data[self::MESS_FLD_LENGTH]);
    }
}