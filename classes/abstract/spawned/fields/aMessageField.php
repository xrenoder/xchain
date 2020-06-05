<?php
/**
 * Base classenum for formats of message
 */
abstract class aMessageField extends aField
{
    protected static $dbgLvl = Logger::DBG_MSG_FLD;

    /** @var string  */
    protected static $parentClass = 'aMessage'; /* overrided */

    /** @var string  */
    protected static $enumClass = 'MessageFieldClassEnum'; /* overrided */

    public function getMessage() : aMessage {return $this->getParent();}
    public function getLegate() : SocketLegate {return $this->getMessage()->getLegate();}

    public static function getStatLength($id) : int {return MessageFieldClassEnum::getLength($id);}
}