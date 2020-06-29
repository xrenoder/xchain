<?php


class TransactionFieldClassEnum extends aFieldClassEnum
{
    protected static $baseClassName = 'aTransactionField'; /* overrided */

// must be serial from 0
    public const TYPE =         0;
    public const AUTHOR =       1;

    public const TARGET =       2;
    public const AMOUNT =       3;

    public const TINY_DATA =    4;
    public const SHORT_DATA =   5;
    public const LONG_DATA =    6;

    public const NONCE =        7;
    public const SIGN =         8;

    protected static $items = array(
        self::TYPE =>   'TypeTransactionField',
        self::AUTHOR => 'AuthorTransactionField',
        self::NONCE =>  'NonceTransactionField',

        self::TARGET => 'TargetTransactionField',
        self::AMOUNT => 'AmountTransactionField',

        self::TINY_DATA => 'TinyDataTransactionField',
        self::SHORT_DATA => 'ShortDataTransactionField',
        self::LONG_DATA => 'LongDataTransactionField',

        self::SIGN =>   'SignTransactionField',
    );

    protected static $data = array(
        self::TYPE =>   FieldFormatClassEnum::UBYTE,          // always must have fixed non-zero length
        self::AUTHOR => FieldFormatClassEnum::ADDR,

        self::TARGET => FieldFormatClassEnum::ADDR,
        self::AMOUNT => FieldFormatClassEnum::UBIG,

        self::TINY_DATA => FieldFormatClassEnum::ASIS_SBYTE,
        self::SHORT_DATA => FieldFormatClassEnum::ASIS_SSHORT,
        self::LONG_DATA => FieldFormatClassEnum::ASIS_SLONG,  // max len of message = MAX_UBIG (2**64-1), max len of transaction message data = MAX_ULONG (2**32-1), max len of transaction data = MAX_USLONG (MAX_ULONG-1024)

        self::NONCE =>  FieldFormatClassEnum::ULONG,
        self::SIGN => FieldFormatClassEnum::ASIS_BYTE,
    );
}