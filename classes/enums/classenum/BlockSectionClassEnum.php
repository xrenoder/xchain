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

    /* 0: formatId of transaction count */
    /* 1: formatId of transaction length */
    protected static $data = array(
        self::SIGNER_PUBLIC_KEYS =>     [FieldFormatClassEnum::USLONG, FieldFormatClassEnum::USHORT],
        self::AUTHOR_FINANCE =>         [FieldFormatClassEnum::MILLION, FieldFormatClassEnum::USHORT],
        self::AUTHOR_DATA =>            [FieldFormatClassEnum::MILLION, FieldFormatClassEnum::ULONG],
        self::AUTHOR_BROADCAST =>       [FieldFormatClassEnum::MILLION, FieldFormatClassEnum::USHORT],
        self::SIGNER_NEXT_SIGNERS =>    [FieldFormatClassEnum::UBYTE, FieldFormatClassEnum::UBYTE],
    );

    public static function getTransactionCountFormatId(int $id) : int
    {
        return self::$data[$id][0];
    }

    public static function getTransactionLengthFormatId(int $id) : int
    {
        return self::$data[$id][1];
    }
}