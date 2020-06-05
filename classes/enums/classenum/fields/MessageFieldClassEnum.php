<?php


class MessageFieldClassEnum extends aFieldClassEnum
{
    protected static $baseClassName = 'aMessageField'; /* overrided */

// must be serial from 0
    public const TYPE =    0;
    public const LENGTH =  1;
    public const NODE =    2;
    public const TIME =    3;
    public const ADDR =    4;
    public const DATA =    5;
    public const PUBKEY =  6;
    public const SIGN =    7;

    public const UNKNOWN_LEN  = 0;
    public const BASE_MAX_LEN  = 1 + 4; // type + length
    public const SIMPLE_MAX_LEN  = 1 + 4 + 1 + 4; // type + length + node + time
    public const SIMPLE_ADDR_MAX_LEN  = 1 + 4 + 1 + 4 + 25; // type + length + node + time + addr

    protected static $items = array(
        self::TYPE =>   'TypeMessageField',
        self::LENGTH => 'LengthMessageField',
        self::NODE =>   'NodeMessageField',
        self::TIME =>   'TimeMessageField',
        self::ADDR =>   'AddrMessageField',
        self::DATA =>   'DataMessageField',
        self::PUBKEY => 'AuthorPublicKeyMessageField',
        self::SIGN =>   'SignMessageField',
    );

    protected static $data = array(
        self::TYPE =>   FieldFormatClassEnum::UBYTE,          // always must have fixed non-zero length
        self::LENGTH => FieldFormatClassEnum::ULONG,      // always must have fixed non-zero length
        self::NODE =>   FieldFormatClassEnum::UBYTE,
        self::TIME =>   FieldFormatClassEnum::ULONG,
        self::ADDR =>   FieldFormatClassEnum::ADDR,
        self::DATA =>   FieldFormatClassEnum::ASIS_SL,  // max len of message = MAX_ULONG (2**32-1), max len of message data (transaction) = (MAX_ULONG-1024), max len of transaction data = (MAX_ULONG-1024*2)
        self::PUBKEY => FieldFormatClassEnum::PUBKEY,
        self::SIGN =>   FieldFormatClassEnum::ASIS_B,
    );
}