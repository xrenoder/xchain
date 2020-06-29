<?php


abstract class aMessageDataField extends aField
{
    protected static $dbgLvl = Logger::DBG_MESSAGE_DATA_FLD;

    /** @var string  */
    protected static $parentClass = 'aMessageData'; /* overrided */

    /** @var string  */
    protected static $enumClass = 'MessageDataFieldClassEnum'; /* overrided */

    public function getMessage() : aDataMessage {return $this->getParent()->getParent();}
}