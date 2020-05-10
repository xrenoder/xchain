<?php
/**
 * Enumeration of field formats
 */

class FieldFormatEnum extends aEnum
{
//    public const NOPACK = 'NP';
    public const UCHAR = 'C';
    public const ULONG_BE = 'N';

    protected static $items = array(
//        self::NOPACK => 'Not packed (variable bytes)',
        self::UCHAR => 'Unsigned char (1 byte)',
        self::ULONG_BE => 'Unsigned long big-endian (4 byte)',
    );

    protected static $data = array(
//        self::NOPACK =>     0,          // undefined, variable length
        self::UCHAR =>      1,
        self::ULONG_BE =>   4,
    );

    public static function getLength(string $id) : int
    {
        return self::$data[$id];
    }

// TODO перенести упаковку/распаковку данных сюда
}