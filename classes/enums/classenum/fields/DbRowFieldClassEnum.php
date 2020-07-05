<?php


class DbRowFieldClassEnum extends aFieldClassEnum
{
    protected static $baseClassName = 'aDbRowField'; /* overrided */

    public const BLOCK =   0;
    public const NODE =    1;
    public const PUBKEY =  2;

    protected static $items = array(
        self::BLOCK =>  'BlockDbRowField',
        self::NODE =>   'NodeDbRowField',
        self::PUBKEY => 'PubKeyDbRowField',
    );

    protected static $data = array(
        self::BLOCK =>  FieldFormatClassEnum::ASIS_BIG,
        self::NODE =>   FieldFormatClassEnum::UBYTE,
        self::PUBKEY => FieldFormatClassEnum::PUBKEY,
    );
}