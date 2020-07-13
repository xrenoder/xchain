<?php


class BlockSectionClassEnum extends aClassEnum
{
    protected static $baseClassName = 'aBlockSection';

    public const SIGNER_PUBLIC_KEYS =   0;
    public const AUTHOR_FINANCE =       1;
    public const AUTHOR_DATA =          2;
    public const AUTHOR_BROADCAST =     3;

    public const SIGNER_NEXT_SIGNERS =  4;

    protected static $items = array(
        self::SIGNER_PUBLIC_KEYS =>     'SignerPublicKeysBlockSection',
        self::AUTHOR_FINANCE =>         'AuthorFinanceBlockSection',
        self::AUTHOR_DATA =>            'AuthorDataBlockSection',
        self::AUTHOR_BROADCAST =>       'AuthorBroadcastBlockSection',
        self::SIGNER_NEXT_SIGNERS =>    'SignerNextSignersBlockSection',
    );

    /* 0: formatType of transaction count */
    /* 1: formatType of transaction raw */
    /* 2: author (true) or signer (false) type of transaction in this block */

    protected static $data = array(
        self::SIGNER_PUBLIC_KEYS =>     [FieldFormatClassEnum::USLONG, FieldFormatClassEnum::ASIS_SHORT, false],
        self::AUTHOR_FINANCE =>         [FieldFormatClassEnum::MILLION, FieldFormatClassEnum::ASIS_SHORT, true],
        self::AUTHOR_DATA =>            [FieldFormatClassEnum::MILLION, FieldFormatClassEnum::ASIS_LONG, true],
        self::AUTHOR_BROADCAST =>       [FieldFormatClassEnum::MILLION, FieldFormatClassEnum::ASIS_SHORT, true],
        self::SIGNER_NEXT_SIGNERS =>    [FieldFormatClassEnum::UBYTE, FieldFormatClassEnum::ASIS_BYTE, false],
    );

    public static function getTransactionCountFormatType(int $type) : int
    {
        return self::$data[$type][0];
    }

    public static function getTransactionRawFormatType(int $type) : int
    {
        return self::$data[$type][1];
    }

    public static function isAuthorTransaction(int $type) : bool
    {
        return self::$data[$type][2];
    }

    public static function needTransactionCheck(int $type) : bool
    {
        return self::$data[$type][3];
    }
}