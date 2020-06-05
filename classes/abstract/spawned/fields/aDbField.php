<?php


abstract class aDbField extends aField
{
    protected static $dbgLvl = Logger::DBG_DB_FLD;

    /** @var string  */
    protected static $parentClass = 'aDbRow'; /* overrided */

    /** @var string  */
    protected static $enumClass = 'DbFieldClassEnum'; /* overrided */

}