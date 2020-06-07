<?php
/**
 * Enumeration of field of DB-row, message, transaction formats
 */

class FieldFormatClassEnum extends aClassEnum
{
    protected static $baseClassName = 'aFieldFormat'; /* overrided */

    public const ASIS =     0; // aFieldFormat + AsIs transform
    public const BINHEX =   1; // aFieldFormat + BinHex transform

    public const BINHEX_L = 2; // aVarLengthFieldFormat + BinHex transform

    public const ADDR =     3;     // aAsIsFixLengthFieldFormat
    public const PUBKEY =   4;     // aAsIsFixLengthFieldFormat
    public const MD4_RAW =  5;   // aAsIsFixLengthFieldFormat

    public const ASIS_B =   6; // aAsIsVarLengthFieldFormat
    public const ASIS_SB =  7; // aAsIsVarLengthFieldFormat
    public const ASIS_S =   8; // aAsIsVarLengthFieldFormat
    public const ASIS_L =   9; // aAsIsVarLengthFieldFormat
    public const ASIS_SL =  10; // aAsIsVarLengthFieldFormat
    public const ASIS_SSL = 11; // aAsIsVarLengthFieldFormat

                                // followed values must be formats from PHP-function pack()
    public const UBYTE =    12; // aFieldFormat (uchar)
    public const USBYTE =   13; // aFieldFormat (uchar)
    public const USHORT =   14; // aFieldFormat (ushort big-endian)
    public const ULONG =    15; // aFieldFormat (ulong big-endian)
    public const USLONG =   16; // aFieldFormat  (ulong little-endian)
    public const USSLONG =  17; // aFieldFormat  (ulong little-endian)
    public const UBIG =     18; // aFieldFormat (ulong-long big-endian)

    public const HOST =     19; // aFixLengthFieldFormat

    private const SUBLONG_DEC =  1024; // value of SubLong and SubSubLong (*2) format max value decrementation
    private const SUBBYTE_DEC =  7; // value of SubByte format max value decrementation

    protected static $items = array(
        self::ASIS =>       'AsIsFormat',  // 'Not packed without declared length (variable bytes). Can be only last field in row.',

        self::BINHEX =>     'BinHexFormat',  // 'Bin converted from/to Hex without declared length (variable bytes). Can be only last field in row.',
        self::BINHEX_L =>   'BinHexLongFormat',  // 'Bin converted from/to Hex with declared length as first 4 bytes ULONG_BE (variable bytes)',

        self::ADDR =>       'AddressFormat',    // 'Not packed with 25 bytes length',
        self::PUBKEY =>     'PubKeyFormat',     // 'Not packed with 248 bytes length',
        self::MD4_RAW =>    'Md4RawFormat',     // 'Not packed with 16 bytes length',

        self::ASIS_B =>     'AsIsByteFormat',  // 'Not packed with declared length as first 1 bytes UBYTE (variable bytes)',
        self::ASIS_SB =>    'AsIsSubByteFormat',  // 'Not packed with declared length as first 1 bytes USBYTE (variable bytes)',
        self::ASIS_S =>     'AsIsShortFormat',  // 'Not packed with declared length as first 2 bytes USHORT_BE (variable bytes)',
        self::ASIS_L =>     'AsIsLongFormat',  // 'Not packed with declared length as first 4 bytes ULONG_BE (variable bytes)',
        self::ASIS_SL =>    'AsIsSubLongFormat',  // 'Not packed with declared length as first 4 bytes USLONG_BE (variable bytes)',
        self::ASIS_SSL =>   'AsIsSubSubLongFormat',  // 'Not packed with declared length as first 4 bytes USSLONG_BE (variable bytes)',

        self::UBYTE =>      'ByteFormat', // 'Unsigned char (1 byte)',
        self::USBYTE =>     'SubByteFormat', // 'Unsigned char (1 byte)',
        self::USHORT =>     'ShortFormat', // 'Unsigned short big-endian (2 bytes)',
        self::ULONG =>      'LongFormat', // 'Unsigned long big-endian (4 bytes)',
        self::USLONG =>     'SubLongFormat', // 'Unsigned long big-endian (4 bytes) - SUBLONG_DEC bytes',
        self::USSLONG =>    'SubSubLongFormat', // 'Unsigned long big-endian (4 bytes) - SUBLONG_DEC * 2 bytes',
        self::UBIG =>       'BigFormat', // 'Unsigned long long big-endian (8 bytes)',

        self::HOST =>       'HostFormat', // 4 bytes for IP, 2 bytes for port
    );

    /* 0: field len or len of field len (if "format of field len" not null) */
    /* 1: format of field len */
    /* 2: maximal value of field
    /* 3: true if field can be only last field in row or message */
    /* 4: pack-function format */
    protected static $data = array(
        self::ASIS =>       [0, null, false, true, null],

        self::BINHEX =>     [0, null, false, true, null],
        self::BINHEX_L =>   [4, self::ULONG, false, false, null],

        self::ADDR =>       [Address::ADDRESS_BIN_LEN, null, false, false, null],
        self::PUBKEY =>     [Address::PUBLIC_BIN_LEN, null, false, false, null],
        self::MD4_RAW =>    [aTransaction::HASH_BIN_LEN, null, false, false, null],

        self::ASIS_B =>     [1, self::UBYTE, false, false, null],
        self::ASIS_SB =>     [1, self::USBYTE, false, false, null],
        self::ASIS_S =>     [2, self::USHORT, false, false, null],
        self::ASIS_L =>     [4, self::ULONG, false, false, null],
        self::ASIS_SL =>    [4, self::USLONG, false, false, null],
        self::ASIS_SSL =>   [4, self::USSLONG, false, false, null],

        self::UBYTE =>      [1, null, 2**8 - 1, false, 'C'],
        self::USBYTE =>      [1, null, 2**8 - self::SUBBYTE_DEC - 1, false, 'C'],
        self::USHORT =>     [2, null, 2**16 - 1, false, 'n'],
        self::ULONG =>      [4, null, 2**32 - 1, false, 'N'],
        self::USLONG =>     [4, null, 2**32 - self::SUBLONG_DEC - 1, false, 'N'],
        self::USSLONG =>    [4, null, 2**32 - self::SUBLONG_DEC * 2 - 1, false, 'N'],
        self::UBIG =>       [8, null, 2**64 - 1, false, 'J'],

        self::HOST =>       [6, null, false, false, null],
    );

    public static function getLength(int $id) : int
    {
        return self::$data[$id][0];
    }

    public static function getLengthFormatId(int $id) : ?int
    {
        return self::$data[$id][1];
    }

    public static function getMaxValue(int $id)
    {
        return self::$data[$id][2];
    }

    public static function isLast(int $id) : bool
    {
        return self::$data[$id][3];
    }

    public static function getPackFormat(int $id) : ?string
    {
        return self::$data[$id][4];
    }
}