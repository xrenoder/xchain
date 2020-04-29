<?php
/**
 * Message fields constants
 */

interface icMessage
{
    public const FLD_LENGTH_LEN = 4;
    public const FLD_LENGTH_FMT = 'N';   //unsigned long big-endian

    public const FLD_TYPE_LEN = 1;
//    public const FLD_TYPE_FMT = 'N';     //unsigned long big-endian (4 bytes)
    public const FLD_TYPE_FMT = 'C';     //unsigned char (1 byte)
}