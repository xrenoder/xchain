<?php
/**
 * Enumeration of field (message and DB) formats
 */

class FieldFormatEnum extends aEnum
{
    public const NOPACK =       'NP';
    public const NOPACK_LBE =   'NPLBE';
    public const BINHEX =       'BH';
    public const BINHEX_LBE =   'BHLBE';

    public const UCHAR =        'C';
    public const ULONG_BE =     'N';

    /* formatId => field length */
    protected static $items = array(
        self::NOPACK =>     0, // 'Not packed without declared length (variable bytes). Can be only last field in row.',
        self::NOPACK_LBE => 4, // 'Not packed with declared length as first 4 bytes ULONG_BE (variable bytes)',
        self::BINHEX =>     0, // 'Bin converted from/to Hex without declared length (variable bytes). Can be only last field in row.',
        self::BINHEX_LBE => 4, // 'Bin converted from/to Hex with declared length as first 4 bytes ULONG_BE (variable bytes)',

        self::UCHAR =>      1, // 'Unsigned char (1 byte)',
        self::ULONG_BE =>   4, // 'Unsigned long big-endian (4 bytes)',
    );

    /* Set if field can be only last field in row or message */
    protected static $data = array(
        self::NOPACK => true,
        self::BINHEX => true,
    );

    public static function getLength(string $id) : int
    {
        return self::$items[$id];
    }

    public static function isLast(string $id) : bool
    {
        if (isset(self::$data[$id])) {
            return true;
        }

        return false;
    }

    public static function pack(string $data, string $id) : string
    {
        switch ($id) {
            case self::NOPACK:
                $result = $data;
                break;
            case self::NOPACK_LBE:
                $result = static::packVariableLength(self::ULONG_BE, $data);
                break;
            case self::BINHEX:
                $result = hex2bin($data);
                break;
            case self::BINHEX_LBE:
                $result = static::packVariableLength(self::ULONG_BE, hex2bin($data));
                break;
            default:
                $result = pack($id, $data);
        }

        return $result;
    }

    private static function packVariableLength($lengthFormat, $data) : string
    {
        $length = strlen($data);
        return pack($lengthFormat, $length) . $data;
    }

    public static function unpack(string $data, string $id, int $offset) : array
    {
        $length = null;
        $result = null;

        switch ($id) {
            case self::NOPACK:
                $length = strlen($data);
                $result = $data;
                break;
            case self::NOPACK_LBE:
                [$length, $fullLength, $realData] = static::unpackVariableLength(self::ULONG_BE, $data, $offset);

                if (strlen($realData) >= $length) {
                    $result = $realData;
                }

                $length = $fullLength;
                break;
            case self::BINHEX:
                $length = strlen($data);
                $result = bin2hex($data);
                break;
            case self::BINHEX_LBE:
                [$length, $fullLength, $realData] = static::unpackVariableLength(self::ULONG_BE, $data, $offset);

                if (strlen($realData) >= $length) {
                    $result = bin2hex($realData);
                }

                $length = $fullLength;
                break;
            default:
                $length = self::$items[$id];
                $result = unpack($id, substr($data, $offset, $length))[1];
        }

        return [$length, $result];
    }

    private static function unpackVariableLength($lengthFormat, $data, $offset) : array
    {
        $lengthFormatLen = self::$items[$lengthFormat];
        $length = unpack($lengthFormat, substr($data, $offset, $lengthFormatLen))[1];

        $realData = substr($data, $offset + $lengthFormatLen, $length);

        $fullLength = $length + $lengthFormatLen;

        return [$length, $fullLength, $realData];
    }
}