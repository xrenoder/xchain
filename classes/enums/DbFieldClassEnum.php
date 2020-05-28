<?php


class DbFieldClassEnum extends aFieldClassEnum
{
    protected static $baseClassName = 'aDbField'; /* overrided */

    public const ASIS =    0;
    public const ADDR =    1;
    public const NODE =    2;
    public const PUBKEY =  3;

    protected static $items = array(
        self::ASIS =>   'DbField',
        self::ADDR =>   'DbField',
        self::NODE =>   'DbField',
        self::PUBKEY => 'DbField',
    );

    protected static $data = array(
        self::ASIS =>   FieldFormatClassEnum::ASIS,
        self::ADDR =>   FieldFormatClassEnum::ADDR,
        self::NODE =>   FieldFormatClassEnum::UCHAR,
        self::PUBKEY => FieldFormatClassEnum::PUBKEY,
    );
}