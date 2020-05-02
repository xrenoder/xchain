<?php


class MessFldEnum extends aEnum implements icMessageFields
{
    public const DATA_LENGTH  = 'len';
    public const DATA_FORMAT  = 'fmt';
    public const DATA_OFFSET  = 'off';
    public const DATA_POINT  = 'pnt';

    public const UNLIMIT_LEN  = 0;
    public const SIMPLE_MAX_LEN  = 1 + 4 + 1;

    protected static $items = array(
        self::MESS_TYPE => 'Type',
        self::MESS_LENGTH => 'Length',
        self::MESS_NODE => 'Node',
    );

    protected static $data = array(
        self::MESS_TYPE => array(
            self::DATA_LENGTH => 1,
            self::DATA_FORMAT => 'C',     // unsigned char (1 byte)
        ),
        self::MESS_LENGTH => array(
            self::DATA_LENGTH => 4,
            self::DATA_FORMAT => 'N',     // unsigned long big-endian (4 bytes)
        ),
        self::MESS_NODE => array(
            self::DATA_LENGTH => 1,
            self::DATA_FORMAT => 'C',     // unsigned char (1 byte)
        ),
    );

    public static function prepareField(int $fieldId, string $str) : int
    {
        $format = static::$data[$fieldId][static::DATA_FORMAT];
        $offset = static::getOffset($fieldId);
        $length = static::$data[$fieldId][static::DATA_LENGTH];
        $tmp = unpack($format, substr($str, $offset, $length));
        return $tmp[1];
    }

    public static function getFormat(int $fieldId) : int
    {
        return static::$data[$fieldId][static::DATA_FORMAT];
    }

    public static function getLength(int $fieldId) : int
    {
        return static::$data[$fieldId][static::DATA_LENGTH];
    }

    public static function getPoint(int $fieldId) : int
    {
        if (!isset(static::$data[$fieldId][static::DATA_POINT])) {
            $point = static::getOffset($fieldId) + static::$data[$fieldId][static::DATA_LENGTH];
            static::$data[$fieldId][static::DATA_POINT] = $point;
        }

        return static::$data[$fieldId][static::DATA_POINT];
    }

    protected static function getOffset(int $fieldId) : int
    {
        if (!isset(static::$data[$fieldId][static::DATA_OFFSET])) {
            $offset = 0;

            for ($i = 0; $i < $fieldId; $i++) {
                $offset += static::$data[$i][static::DATA_LENGTH];
            }

            static::$data[$fieldId][static::DATA_OFFSET] = $offset;
        }

        return static::$data[$fieldId][static::DATA_OFFSET];
    }


}