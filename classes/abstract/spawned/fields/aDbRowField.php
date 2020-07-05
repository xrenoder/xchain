<?php


abstract class aDbRowField extends aField
{
    protected static $dbgLvl = Logger::DBG_DB_FLD;

    /** @var string  */
    protected static $parentClass = 'aDbRow'; /* overrided */

    /** @var string  */
    protected static $enumClass = 'DbRowFieldClassEnum'; /* overrided */

    public function getRow() : aDbRow {return $this->getParent();}
}