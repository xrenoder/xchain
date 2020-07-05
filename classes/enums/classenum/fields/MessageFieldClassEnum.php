<?php


class MessageFieldClassEnum extends aFieldClassEnum
{
    protected static $baseClassName = 'aMessageField'; /* overrided */

// must be serial from 0
    public const TYPE =    0;
    public const LENGTH =  1;
    public const NODE =    2;
    public const TIME =    3;
    public const SENDER =  4;
    public const DATA =    5;
    public const PUBKEY =  6;
    public const SIGN =    7;

    protected static $items = array(
        self::TYPE =>   'TypeMessageField',
        self::LENGTH => 'LengthMessageField',
        self::NODE =>   'NodeMessageField',
        self::TIME =>   'TimeMessageField',
        self::SENDER => 'SenderMessageField',
        self::DATA =>   'DataMessageField',
        self::PUBKEY => 'AuthorPublicKeyMessageField',
        self::SIGN =>   'SignMessageField',
    );

    protected static $data = array(
        self::TYPE =>   FieldFormatClassEnum::UBYTE,        // always must have fixed non-zero length
        self::LENGTH => FieldFormatClassEnum::UBIG,         // always must have fixed non-zero length
        self::NODE =>   FieldFormatClassEnum::UBYTE,
        self::TIME =>   FieldFormatClassEnum::ULONG,
        self::SENDER => FieldFormatClassEnum::ADDR,
        self::DATA =>   FieldFormatClassEnum::ASIS_SBIG,    // max len of message = MAX_UBIG (2**64-1), max len of block message data = MAX_USBIG (2**64-1-1024)
        self::PUBKEY => FieldFormatClassEnum::PUBKEY,
        self::SIGN =>   FieldFormatClassEnum::ASIS_BYTE,
    );

    public static function getBaseMaxLen()
    {
        return
            FieldFormatClassEnum::getLength(static::getFormat(self::TYPE))
            + FieldFormatClassEnum::getLength(static::getFormat(self::LENGTH));
    }

    public static function getSimpleMaxLen()
    {
        return
            FieldFormatClassEnum::getLength(static::getFormat(self::TYPE))
            + FieldFormatClassEnum::getLength(static::getFormat(self::LENGTH))
            + FieldFormatClassEnum::getLength(static::getFormat(self::NODE))
            + FieldFormatClassEnum::getLength(static::getFormat(self::TIME));
    }

    public static function getSimpleAddrMaxLen()
    {
        return
            FieldFormatClassEnum::getLength(static::getFormat(self::TYPE))
            + FieldFormatClassEnum::getLength(static::getFormat(self::LENGTH))
            + FieldFormatClassEnum::getLength(static::getFormat(self::NODE))
            + FieldFormatClassEnum::getLength(static::getFormat(self::TIME))
            + FieldFormatClassEnum::getLength(static::getFormat(self::SENDER));
    }
}