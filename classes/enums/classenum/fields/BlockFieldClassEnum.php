<?php


class BlockFieldClassEnum extends aFieldClassEnum
{
    protected static $baseClassName = 'aBlockField'; /* overrided */

// must be serial from 0
    public const ID =           0;
    public const CHAIN =        1;
    public const TIME =         2;
    public const SIGNER =       3;
    public const SIGN =         4;

    protected static $items = array(
        self::ID =>             'IdBlockField',
        self::CHAIN =>          'ChainBlockField',
        self::TIME =>           'TimeBlockField',
        self::SIGNER =>         'SignerBlockField',
        self::SIGN =>           'SignBlockField',
    );

    protected static $data = array(
        self::ID =>             FieldFormatClassEnum::UBIG,
        self::CHAIN =>          FieldFormatClassEnum::UBIG,
        self::TIME =>           FieldFormatClassEnum::ULONG,
        self::SIGNER =>         FieldFormatClassEnum::ADDR,
        self::SIGN =>           FieldFormatClassEnum::ASIS_SBYTE,
    );
}