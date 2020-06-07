<?php


class TransactionDataFieldClassEnum extends aFieldClassEnum
{
    protected static $baseClassName = 'aTransactionDataField'; /* overrided */

    public const PUBKEY =       0;
    public const HOST =         1;
    public const NODE_NAME =    2;


    protected static $items = array(
        self::PUBKEY =>     'PubKeyTransactionDataField',
        self::HOST =>       'HostTransactionDataField',
        self::NODE_NAME =>  'NodeNameTransactionDataField',
    );

    protected static $data = array(
        self::PUBKEY =>         FieldFormatClassEnum::PUBKEY,
        self::HOST =>           FieldFormatClassEnum::HOST,
        self::NODE_NAME =>      FieldFormatClassEnum::ASIS_SB,
    );
}