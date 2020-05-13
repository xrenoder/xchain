<?php


class LastKnownBlockRow extends aDbRow
{
    /** @var string  */
    protected static $table = self::SUMMARY_TABLE;     /* overrided */

    /** @var string  */
    protected $id = self::LAST_KNOWN_BLOCK; /* overrided */
    protected $idFormat = FieldFormatEnum::NOPACK; /* overrided */

    protected static $canBeReplaced = true;     /* overrided */
}