<?php
/**
 * Enumeration of field formats
 */

class FieldFormatEnum extends aEnum
{
    public const UCHAR = 'C';
    public const ULONG_BE = 'N';

    protected static $items = array(
        self::UCHAR => 'Unsigned char (1 byte)',
        self::ULONG_BE => 'Unsigned long big-endian (4 byte)',
    );

    protected static $data = array(
        self::UCHAR =>      1,
        self::ULONG_BE =>   4,
    );

    public static function getLength(int $id) : int
    {
        return self::$data[$id];
    }
}