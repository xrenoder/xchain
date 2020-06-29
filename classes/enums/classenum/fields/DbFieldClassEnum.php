<?php


class DbFieldClassEnum extends aFieldClassEnum
{
    protected static $baseClassName = 'aDbField'; /* overrided */

    public const BLOCK =   0;
    public const NODE =    1;
    public const PUBKEY =  2;

    protected static $items = array(
        self::BLOCK =>  'BlockDbField',
        self::NODE =>   'NodeDbField',
        self::PUBKEY => 'PubKeyDbField',
    );

    protected static $data = array(
        self::BLOCK =>  FieldFormatClassEnum::ASIS_BIG,
        self::NODE =>   FieldFormatClassEnum::UBYTE,
        self::PUBKEY => FieldFormatClassEnum::PUBKEY,
    );
}