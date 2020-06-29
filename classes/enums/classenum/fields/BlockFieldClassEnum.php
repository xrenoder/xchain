<?php


class BlockFieldClassEnum extends aFieldClassEnum
{
    protected static $baseClassName = 'aBlockField'; /* overrided */

// must be serial from 0
    public const NUMBER =       0;
    public const CHAIN =        1;
    public const TIME =         2;
    public const PREV_SIGN =    3;
    public const SIGNER_ADDR =  4;
    public const SIGN =         5;

    public const FIRST_SECTION_REACHED = self::SIGNER_ADDR;

    protected static $items = array(
        self::NUMBER =>     'NumberBlockField',
        self::CHAIN =>      'ChainBlockField',
        self::TIME =>       'TimeBlockField',
        self::PREV_SIGN =>  'PrevSignBlockField',
        self::SIGNER_ADDR =>'SignerAddrBlockField',
        self::SIGN =>       'SignBlockField',
    );

    protected static $data = array(
        self::NUMBER =>         FieldFormatClassEnum::UBIG,
        self::CHAIN =>          FieldFormatClassEnum::ULONG,
        self::TIME =>           FieldFormatClassEnum::ULONG,
        self::PREV_SIGN =>      FieldFormatClassEnum::ASIS_SBYTE,
        self::SIGNER_ADDR =>    FieldFormatClassEnum::ADDR,
        self::SIGN =>           FieldFormatClassEnum::ASIS_SBYTE,
    );
}