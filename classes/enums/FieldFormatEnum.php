<?php
/**
 * Enumeration of field (message and DB) formats
 */

class FieldFormatEnum extends aEnum
{
    public const MAX_UCHAR =    255;

    public const NOPACK =       'NP';
    public const NOPACK_LBE =   'NPLBE';
    public const BINHEX =       'BH';
    public const BINHEX_LBE =   'BHLBE';

    public const ADDR =         'ADDR';
    public const PUBKEY =       'PUBK';
    public const SIGN_LC =      'SIGN';

    public const UCHAR =        'C';
    public const ULONG_BE =     'N';

    /* formatId => field length */
    protected static $items = array(
        self::NOPACK =>     0,  // 'Not packed without declared length (variable bytes). Can be only last field in row.',
        self::NOPACK_LBE => 4,  // 'Not packed with declared length as first 4 bytes ULONG_BE (variable bytes)',
        self::BINHEX =>     0,  // 'Bin converted from/to Hex without declared length (variable bytes). Can be only last field in row.',
        self::BINHEX_LBE => 4,  // 'Bin converted from/to Hex with declared length as first 4 bytes ULONG_BE (variable bytes)',

        self::ADDR =>       Address::ADDRESS_BIN_LEN, // 'Not packed with 25 bytes length',
        self::PUBKEY =>     Address::PUBLIC_BIN_LEN,// 'Not packed with 248 bytes length',
        self::SIGN_LC =>    1,  // 'Not packed with declared length as first 1 bytes UCHAR (variable bytes)',

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

// TODO весь этот кошмар, что я нахуевертил ниже, перенести в классы форматов полей, а данный класс преобразовать в aClassEnum

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
            case self::ADDR:
                $result = $data;

                if (strlen($result) !== self::$items[$id]) {
                    throw new Exception("Bad value length of packed field '$id': " . strlen($result) . ' instead ' . self::$items[$id]);
                }

                break;
            case self::SIGN_LC:
                $result = static::packVariableLength(self::UCHAR, $data);

                if (strlen($result) > static::MAX_UCHAR + 1) {
                    throw new Exception("Bad value length of field '$id': " . strlen($result) . ' more than ' . static::MAX_UCHAR);
                }

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
        $raw = null;

        switch ($id) {
            case self::NOPACK:
                $raw = $data;
                $length = strlen($data);
                $result = $data;
                break;

            case self::BINHEX:
                $raw = $data;
                $length = strlen($data);
                $result = bin2hex($data);
                break;

            case self::NOPACK_LBE:
                [$length, $fullLength, $raw] = static::unpackVariableLength(self::ULONG_BE, $data, $offset);

                if (strlen($raw) >= $length) {
                    $result = $raw;
                }

                $length = $fullLength;
                break;

            case self::BINHEX_LBE:
                [$length, $fullLength, $raw] = static::unpackVariableLength(self::ULONG_BE, $data, $offset);

                if (strlen($raw) >= $length) {
                    $result = bin2hex($raw);
                }

                $length = $fullLength;
                break;

            case self::ADDR:
            case self::PUBKEY:
                $length = self::$items[$id];

                if (strlen(substr($data, $offset)) >= $length) {
                    $raw = substr($data, $offset, $length);
                    $result = $raw;
                }

                break;

            case self::SIGN_LC:
                [$length, $fullLength, $raw] = static::unpackVariableLength(self::UCHAR, $data, $offset);

                if (strlen($raw) >= $length) {
                    $result = $raw;
                }

                $length = $fullLength;
                break;
            default:
                $length = self::$items[$id];
                $raw = substr($data, $offset, $length);
                $result = unpack($id, $raw)[1];
        }

        return [$length, $result, $raw];
    }

    private static function unpackVariableLength($lengthFormat, $data, $offset) : array
    {
        $lengthFormatLen = self::$items[$lengthFormat];
        $length = unpack($lengthFormat, substr($data, $offset, $lengthFormatLen))[1];

        $raw = substr($data, $offset + $lengthFormatLen, $length);

        $fullLength = $length + $lengthFormatLen;

        return [$length, $fullLength, $raw];
    }
}