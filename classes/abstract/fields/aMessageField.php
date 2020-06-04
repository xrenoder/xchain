<?php
/**
 * Base classenum for formats of message
 */
abstract class aMessageField extends aField
{
    protected static $dbgLvl = Logger::DBG_MSG_FLD;

    /** @var string  */
    protected $enumClass = 'MessageFieldClassEnum'; /* overrided */

    public function getMessage() : aMessage {return $this->getParent();}
    public function getLegate() : SocketLegate {return $this->getMessage()->getLegate();}

    public static function getStatLength($id) : int {return MessageFieldClassEnum::getLength($id);}

    public static function spawn(aMessage $message, int $id, int $offset = 0) : self
    {
        /** @var aMessageField $className */

        if ($className = MessageFieldClassEnum::getClassName($id)) {
            return $className::create($message, $offset);
        }

        throw new Exception("Bad code - unknown message field classenum for ID " . $id);
    }
}