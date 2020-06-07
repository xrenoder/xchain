<?php


class TransactionDataClassEnum extends aClassEnum
{
    protected static $baseClassName = 'aTransactionData'; /* overrided */

    public const PUBLIC_KEY =       0;
    public const NODE_HOST_NAME =   1;

    protected static $items = array(
        self::PUBLIC_KEY =>      'PublicKeyTransactionData',
        self::NODE_HOST_NAME =>  'NodeHostNameTransactionData',
    );
}