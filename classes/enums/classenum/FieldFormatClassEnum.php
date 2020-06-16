<?php
/**
 * Enumeration of field of DB-row, message, transaction formats
 */

class FieldFormatClassEnum extends aClassEnum
{
    protected static $baseClassName = 'aFieldFormat'; /* overrided */

    private const SUBBYTE_MAX =  105; // value of SubByte format max value
    private const SUBSHORT_DEC =  1024; // value of SubShort format max value decrementation
    private const SUBLONG_DEC =  1024; // value of SubLong format max value decrementation
    private const SUBBIG_DEC =  1024; // value of SubBig format max value decrementation

    public const ASIS =     0; // aFieldFormat + AsIs transform
    public const BINHEX =   1; // aFieldFormat + BinHex transform

    public const BINHEX_LONG = 2; // aVarLengthFieldFormat + BinHex transform

    public const ADDR =     3;     // aAsIsFixLengthFieldFormat
    public const PUBKEY =   4;     // aAsIsFixLengthFieldFormat
    public const MD4_RAW =  5;   // aAsIsFixLengthFieldFormat

    public const ASIS_BYTE = 6; // aAsIsVarLengthFieldFormat
    public const ASIS_SBYTE =   7; // aAsIsVarLengthFieldFormat
    public const ASIS_64 =   8; // aAsIsVarLengthFieldFormat
    public const ASIS_SHORT =   9; // aAsIsVarLengthFieldFormat
    public const ASIS_SSHORT =   10; // aAsIsVarLengthFieldFormat
    public const ASIS_LONG =   11; // aAsIsVarLengthFieldFormat
    public const ASIS_SLONG =  12; // aAsIsVarLengthFieldFormat
    public const ASIS_BIG = 13; // aAsIsVarLengthFieldFormat
    public const ASIS_SBIG = 14; // aAsIsVarLengthFieldFormat

                                // followed values must be formats from PHP-function pack()
    public const UBYTE =    15; // aFieldFormat (uchar)
    public const USBYTE =   16; // aFieldFormat (uchar)
    public const SIXTY_FOUR =   17; // aFieldFormat (uchar)
    public const USHORT =   18; // aFieldFormat (ushort big-endian)
    public const USSHORT =  19; // aFieldFormat (ushort big-endian)
    public const ULONG =    20; // aFieldFormat (ulong big-endian)
    public const USLONG =   21; // aFieldFormat  (ulong big-endian)
    public const MILLION =  22; // aFieldFormat  (ulong big-endian)
    public const UBIG =     23; // aFieldFormat (ulong-long big-endian)
    public const USBIG =    24; // aFieldFormat (ulong-long big-endian)

    public const HOST =     25; // aFixLengthFieldFormat


    protected static $items = array(
        self::ASIS =>       'AsIsFormat',  // 'Not packed without declared length (variable bytes). Can be only last field in row.',

        self::BINHEX =>     'BinHexFormat',  // 'Bin converted from/to Hex without declared length (variable bytes). Can be only last field in row.',
        self::BINHEX_LONG =>   'BinHexLongFormat',  // 'Bin converted from/to Hex with declared length as first 4 bytes ULONG_BE (variable bytes)',

        self::ADDR =>       'AddressFormat',    // 'Not packed with 25 bytes length',
        self::PUBKEY =>     'PubKeyFormat',     // 'Not packed with 248 bytes length',
        self::MD4_RAW =>    'Md4RawFormat',     // 'Not packed with 16 bytes length',

        self::ASIS_BYTE =>     'AsIsByteFormat',  // 'Not packed with declared length as first 1 bytes UBYTE (variable bytes)',
        self::ASIS_SBYTE =>    'AsIsSubByteFormat',  // 'Not packed with declared length as first 1 bytes USBYTE (variable bytes)',
        self::ASIS_64 =>    'AsIsSixtyFourFormat',  // 'Not packed with declared length as first 1 bytes SIXTY_FOUR (variable bytes)',
        self::ASIS_SHORT =>     'AsIsShortFormat',  // 'Not packed with declared length as first 2 bytes USHORT (variable bytes)',
        self::ASIS_SSHORT =>     'AsIsSubShortFormat',  // 'Not packed with declared length as first 2 bytes USSHORT (variable bytes)',
        self::ASIS_LONG =>     'AsIsLongFormat',  // 'Not packed with declared length as first 4 bytes ULONG (variable bytes)',
        self::ASIS_SLONG =>    'AsIsSubLongFormat',  // 'Not packed with declared length as first 4 bytes USLONG (variable bytes)',
        self::ASIS_BIG =>   'AsIsBigFormat',  // 'Not packed with declared length as first 8 bytes UBIG (variable bytes)',
        self::ASIS_SBIG =>   'AsIsSubBigFormat',  // 'Not packed with declared length as first 8 bytes USBIG (variable bytes)',

        self::UBYTE =>      'ByteFormat', // 'Unsigned char (1 byte)',
        self::USBYTE =>     'SubByteFormat', // 'Unsigned char (1 byte) SUBBYTE_MAX maximal value',
        self::SIXTY_FOUR => 'SixtyFourFormat', // 'Unsigned char (1 byte) 64 maximal value',
        self::USHORT =>     'ShortFormat', // 'Unsigned short big-endian (2 bytes)',
        self::USSHORT =>    'SubShortFormat', // 'Unsigned short big-endian (2 bytes) - SUBSHORT_DEC maximal value',
        self::ULONG =>      'LongFormat', // 'Unsigned long big-endian (4 bytes)',
        self::USLONG =>     'SubLongFormat', // 'Unsigned long big-endian (4 bytes) - SUBLONG_DEC maximal value',
        self::MILLION =>    'MillionFormat', // 'Unsigned long big-endian (4 bytes) one million maximal value',
        self::UBIG =>       'BigFormat', // 'Unsigned long long big-endian (8 bytes)',
        self::USBIG =>      'SubBigFormat', // 'Unsigned long long big-endian (8 bytes) - SUBBIG_DEC maximal value',

        self::HOST =>       'HostFormat', // 4 bytes for IP, 2 bytes for port
    );

    /* 0: field len or len of field len (if "format of field len" not null) */
    /* 1: format of field len */
    /* 2: maximal value of field
    /* 3: true if field can be only last field in fieldset */
    /* 4: pack-function format */
    protected static $data = array(
        self::ASIS =>           [0, null, false, true, null],

        self::BINHEX =>         [0, null, false, true, null],
        self::BINHEX_LONG =>    [4, self::ULONG, false, false, null],

        self::ADDR =>           [Address::ADDRESS_BIN_LEN, null, false, false, null],
        self::PUBKEY =>         [Address::PUBLIC_BIN_LEN, null, false, false, null],
        self::MD4_RAW =>        [aTransaction::HASH_BIN_LEN, null, false, false, null],

        self::ASIS_BYTE =>      [1, self::UBYTE, false, false, null],
        self::ASIS_SBYTE =>     [1, self::USBYTE, false, false, null],
        self::ASIS_64 =>        [1, self::SIXTY_FOUR, false, false, null],
        self::ASIS_SHORT =>     [2, self::USHORT, false, false, null],
        self::ASIS_SSHORT =>    [2, self::USSHORT, false, false, null],
        self::ASIS_LONG =>      [4, self::ULONG, false, false, null],
        self::ASIS_SLONG =>     [4, self::USLONG, false, false, null],
        self::ASIS_BIG =>       [8, self::UBIG, false, false, null],
        self::ASIS_SBIG =>      [8, self::USBIG, false, false, null],

        self::UBYTE =>          [1, null, 2**8 - 1, false, 'C'],
        self::USBYTE =>         [1, null, self::SUBBYTE_MAX, false, 'C'],
        self::SIXTY_FOUR =>     [1, null, 64, false, 'C'],
        self::USHORT =>         [2, null, 2**16 - 1, false, 'n'],
        self::USSHORT =>        [2, null, 2**16 - self::SUBSHORT_DEC - 1, false, 'n'],
        self::ULONG =>          [4, null, 2**32 - 1, false, 'N'],
        self::USLONG =>         [4, null, 2**32 - self::SUBLONG_DEC - 1, false, 'N'],
        self::MILLION =>        [4, null, 1000000, false, 'N'],
        self::UBIG =>           [8, null, 2**64 - 1, false, 'J'],
        self::USBIG =>          [8, null, 2**64 - self::SUBBIG_DEC - 1, false, 'J'],

        self::HOST =>           [6, null, false, false, null],
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