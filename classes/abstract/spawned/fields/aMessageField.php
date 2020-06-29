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

    public static function getStaticLength($type) : int {return MessageFieldClassEnum::getLength($type);}

    public function postPrepare() :  bool
    {
        /* @var aMessage $message */
        $message = $this->getMessage();
        $message->setSignedData($message->getSignedData() . $this->getRawWithLength());

        return true;
    }
}