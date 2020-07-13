<?php


class DbRowFieldClassEnum extends aFieldClassEnum
{
    protected static $baseClassName = 'aDbRowField'; /* overrided */

    public const BLOCK =        0;
    public const PUBKEY =       1;
    public const TRANSACTION =  2;

    protected static $items = array(
        self::BLOCK =>  'BlockDbRowField',
        self::PUBKEY => 'PubKeyDbRowField',
        self::TRANSACTION => 'TransactionDbRowField',
    );

    protected static $data = array(
        self::BLOCK =>  FieldFormatClassEnum::ASIS_BIG,
        self::PUBKEY => FieldFormatClassEnum::PUBKEY,
        self::TRANSACTION =>  FieldFormatClassEnum::ASIS_LONG,
    );
}