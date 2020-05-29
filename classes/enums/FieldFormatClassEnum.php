<?php
/**
 * Enumeration of field of DB-row, message, transaction formats
 */

class FieldFormatClassEnum extends aClassEnum
{
    protected static $baseClassName = 'aFieldFormat'; /* overrided */

    public const ASIS =       'AI'; // aFieldFormat + AsIs transform
    public const BINHEX =     'BH'; // aFieldFormat + BinHex transform

    public const BINHEX_LBE = 'BHLBE'; // aVarLengthFieldFormat + BinHex transform

    public const ADDR =         'ADDR'; // aAsIsFixLengthFieldFormat
    public const PUBKEY =       'PUBK'; // aAsIsFixLengthFieldFormat

    public const ASIS_LBE =   'AILBE'; // aAsIsVarLengthFieldFormat
    public const SIGN_LC =    'SIGN'; // aAsIsVarLengthFieldFormat

                                // followed values must be formats from PHP-function pack()
    public const UCHAR =        'C'; // aFieldFormat
    public const ULONG_BE =     'N'; // aFieldFormat

    protected static $items = array(
        self::ASIS =>     'AsIsFormat',  // 'Not packed without declared length (variable bytes). Can be only last field in row.',
        self::ASIS_LBE => 'AsIsLongFormat',  // 'Not packed with declared length as first 4 bytes ULONG_BE (variable bytes)',
        self::BINHEX =>     'BinHexFormat',  // 'Bin converted from/to Hex without declared length (variable bytes). Can be only last field in row.',
        self::BINHEX_LBE => 'BinHexLongFormat',  // 'Bin converted from/to Hex with declared length as first 4 bytes ULONG_BE (variable bytes)',

        self::ADDR =>       'AddressFormat', // 'Not packed with 25 bytes length',
        self::PUBKEY =>     'PubKeyFormat',// 'Not packed with 248 bytes length',
        self::SIGN_LC =>    'SignFormat',  // 'Not packed with declared length as first 1 bytes UCHAR (variable bytes)',

        self::UCHAR =>      'ByteFormat', // 'Unsigned char (1 byte)',
        self::ULONG_BE =>   'LongFormat', // 'Unsigned long big-endian (4 bytes)',
    );

    /* 0: field len or len of field len */
    /* 1: format of field len */
    /* 2: maximal value of field
    /* 3: true if field can be only last field in row or message */
    protected static $data = array(
        self::ASIS =>     [0, null, false, true],  // 'Not packed without declared length (variable bytes). Can be only last field in row.',
        self::ASIS_LBE => [4, self::ULONG_BE, false, false],  // 'Not packed with declared length as first 4 bytes ULONG_BE (variable bytes)',
        self::BINHEX =>     [0, null, false, true],  // 'Bin converted from/to Hex without declared length (variable bytes). Can be only last field in row.',
        self::BINHEX_LBE => [4, self::ULONG_BE, false, false],  // 'Bin converted from/to Hex with declared length as first 4 bytes ULONG_BE (variable bytes)',

        self::ADDR =>       [Address::ADDRESS_BIN_LEN, null, false, false], // 'Not packed with 25 bytes length',
        self::PUBKEY =>     [Address::PUBLIC_BIN_LEN, null, false, false],// 'Not packed with 248 bytes length',
        self::SIGN_LC =>    [1, self::UCHAR, false, false],  // 'Not packed with declared length as first 1 bytes UCHAR (variable bytes)',

        self::UCHAR =>      [1, null, 2^8 - 1, false], // 'Unsigned char (1 byte)',
        self::ULONG_BE =>   [4, null, 2^31 - 1, false], // 'Unsigned long big-endian (4 bytes)',
    );

    public static function getLength(string $id) : int
    {
        return self::$data[$id][0];
    }

    public static function getLengthFormatId(string $id) : ?string
    {
        return self::$data[$id][1];
    }

    public static function getMaxValue(string $id)
    {
        return self::$data[$id][2];
    }

    public static function isLast(string $id) : bool
    {
        return self::$data[$id][3];
    }
}