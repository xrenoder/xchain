<?php

abstract class aFixedIdDbRow extends aDbRow
{
    protected static $dbgLvl = Logger::DBG_DB_FROW;

    /** @var string  */
    protected $table = DbTableEnum::SUMMARY;     /* overrided */

    /** @var string  */
    protected static $enumClass = 'FixedIdDbRowClassEnum'; /* overrided */

    protected $idFormatType = FieldFormatClassEnum::ASIS; /* can be overrided */

    protected $canBeReplaced = true;     /* overrided */

    public static function create(aBase $parent) : self
    {
        $me = new static($parent);

        $me
            ->setTypeFromEnum()
            ->load();

        return $me;
    }
}