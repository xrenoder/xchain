<?php
/**
 * Base class for fields of message
 */
abstract class aMessageField extends aBase
{
    protected static $dbgLvl = Logger::DBG_MSG_FLD;

    /** @var int  */
    protected static $id;  /* override me */

    public function getMessage() : aMessage {return $this->getParent();}
    public function getSocket() : Socket {return $this->getMessage()->getParent();}

    /** @var string */
    protected $name;
    public function setName() : self {$this->name = MessageFieldClassEnum::getItem(static::$id); return $this;}

    /** @var string  */
    protected $format = null;
    public function setFormat() : self {$this->format = MessageFieldClassEnum::getFormat(static::$id); return $this;}

    /** @var int  */
    protected $length = null;
    public function setLength() : self {$this->length = MessageFieldClassEnum::getLength(static::$id); return $this;}

    /** @var int  */
    protected $offset = null;

    /** @var int  */
    protected $point = null;
    public function getPoint() {return $this->point;}

    protected $value = null;

    abstract public function check() : bool;

    public function setOffsetAndPoint() : self
    {
        $this->offset = MessageFieldClassEnum::getOffset(static::$id);
        $this->point = $this->offset + $this->length;
        return $this;
    }

    protected static function create(aMessage $message) : ?self
    {
        $me = new static($message);

        $me
            ->setFormat()
            ->setLength()
            ->setOffsetAndPoint()
            ->setName();

        $message->dbg(MessageFieldClassEnum::getItem(static::$id) .  ' object created');

        return $me;
    }

    public static function spawn(aMessage $message, int $id) : ?self
    {
        /** @var aMessageField $className */

        if ($className = MessageFieldClassEnum::getClassName($id)) {
            return $className::create($message);
        }

        return null;
    }

    public function unpackField(string $str)
    {
        $this->value = unpack($this->format, substr($str, $this->offset, $this->length))[1];

        return $this->value;
    }

    public static function packField($val)
    {
        return pack(MessageFieldClassEnum::getFormat(static::$id), $val);
    }

    public static function getLength() : int
    {
        return MessageFieldClassEnum::getLength(static::$id);
    }
}