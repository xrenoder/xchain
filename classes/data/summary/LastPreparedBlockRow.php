<?php


class LastPreparedBlockRow extends aDbRow
{
    /** @var string  */
    protected static $table = self::SUMMARY_TABLE;     /* overrided */

    /** @var string  */
    protected $id = self::LAST_PREPARED_BLOCK; /* overrided */

    protected static $canBeReplaced = true;     /* overrided */
}