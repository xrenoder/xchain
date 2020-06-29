<?php


abstract class aBlockField extends aField
{
    protected static $dbgLvl = Logger::DBG_BLOCK_FLD;

    /** @var string  */
    protected static $parentClass = 'aBlock'; /* overrided */

    /** @var string  */
    protected static $enumClass = 'BlockFieldClassEnum'; /* overrided */

    public function getBlock() : Block {return $this->getParent();}
}