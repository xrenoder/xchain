<?php


abstract class aDbField extends aField
{
    protected static $dbgLvl = Logger::DBG_DB_FLD;

    /** @var string  */
    protected $enumClass = 'DbFieldClassEnum'; /* overrided */

    public static function spawn(aDbRow $row, int $id, int $offset) : self
    {
        /** @var aDbField $className */

        if ($className = DbFieldClassEnum::getClassName($id)) {
            return $className::create($row, $offset);
        }

        throw new Exception("Bad code - unknown DB field classenum for fixed ID " . $id);
    }
}