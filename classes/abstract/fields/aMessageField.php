<?php
/**
 * Base class for formats of message
 */
abstract class aMessageField extends aField
{
    protected static $dbgLvl = Logger::DBG_MSG_FLD;

    public function getMessage() : aMessage {return $this->getParent();}
    public function getLegate() : SocketLegate {return $this->getMessage()->getLegate();}

    public function setName() : aField {$this->name = MessageFieldClassEnum::getItem($this->id); return $this;}

    public static function getStatLength($id) : int {return MessageFieldClassEnum::getLength($id);}

    public static function spawn(aBase $message, int $id, int $offset = 0) : self
    {
        /** @var aMessageField $className */

        if ($className = MessageFieldClassEnum::getClassName($id)) {
            return $className::create($message, $offset);
        }

        throw new Exception("Bad code - unknown message field class for ID " . $id);
    }
}