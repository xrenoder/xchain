<?php


class TransactionClassEnum extends aClassEnum
{
    protected static $baseClassName = 'aTransaction'; /* overrided */

    public const REGISTER_PUBLIC_KEY = 0;
    public const REGISTER_NODE_HOST = 1;

    protected static $items = array(
        self::REGISTER_PUBLIC_KEY =>    'RegisterPublicKeyTransaction',     // AT
        self::REGISTER_NODE_HOST =>     'RegisterNodeHostTransaction',      // CT
    );

    /* 'transactionId' => 'blockSectionId' */
    protected static $data = array(
        self::REGISTER_PUBLIC_KEY => BlockSectionClassEnum::SIGNER_PUBLIC_KEYS,
        self::REGISTER_NODE_HOST => BlockSectionClassEnum::AUTHOR_BROADCAST,
    );

    public static function getBlockSectionType(int $type) : int
    {
        return self::$data[$type];
    }

    public static function getMaxTransactionLength(int $type) : int
    {
        return FieldFormatClassEnum::getMaxValue(
            BlockSectionClassEnum::getTransactionRawFormatType(
                static::getBlockSectionType($type)
            )
        );
    }
}