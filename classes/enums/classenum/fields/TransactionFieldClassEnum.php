<?php


class TransactionFieldClassEnum extends aFieldClassEnum
{
    protected static $baseClassName = 'aTransactionField'; /* overrided */

// must be serial from 0
    public const TYPE =     0;
    public const AUTHOR =   1;

    public const SIGN =     6;
    public const HASH =     7;

    protected static $items = array(
        self::TYPE =>   'TypeTransactionField',
        self::AUTHOR => 'AuthorTransactionField',

        self::SIGN =>   'SignTransactionField',
    );

    protected static $data = array(
        self::TYPE =>   FieldFormatClassEnum::UCHAR,          // always must have fixed non-zero length
        self::AUTHOR => FieldFormatClassEnum::ADDR,

        self::SIGN => FieldFormatClassEnum::SIGN_LC,
        self::HASH => FieldFormatClassEnum::MD4_RAW,
    );
}