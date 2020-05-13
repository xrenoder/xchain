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
    public function getName() : string {return $this->name;}

    /** @var string  */
    protected $format = null;
    public function setFormat() : self {$this->format = MessageFieldClassEnum::getFormat(static::$id); return $this;}

    /** @var int  */
    protected $length = null;
    public function setLength($val) : self {$this->length = $val; return $this;}

    /** @var int  */
    protected $offset = null;
    public function setOffset($val) : self {$this->offset = $val; return $this;}

    /** @var int  */
    protected $point = null;
    public function getPoint() {return $this->point;}
    public function setPoint() : self {$this->point = $this->offset + $this->length; return $this;}

    /** @var bool */
    protected $isLast;
    public function setIsLast() : self {$this->isLast = FieldFormatEnum::isLast($this->format); return $this;}
    public function isLast() : bool {return $this->isLast;}

    protected $value = null;

    abstract public function check() : bool;

    protected static function create(aMessage $message, int $offset) : ?self
    {
        $me = new static($message);

        $me
            ->setFormat()
            ->setIsLast()
            ->setLength(MessageFieldClassEnum::getLength(static::$id))
            ->setOffset($offset)
            ->setPoint()
            ->setName();

        $message->dbg(MessageFieldClassEnum::getItem(static::$id) .  " object created (offset $offset)");

        return $me;
    }

    public static function spawn(aMessage $message, int $id, int $offset) : ?self
    {
        /** @var aMessageField $className */

        if ($className = MessageFieldClassEnum::getClassName($id)) {
            return $className::create($message, $offset);
        }

        return null;
    }

    public function unpackField(string $str) : array
    {
        [$length, $this->value] = FieldFormatEnum::unpack($str, $this->format, $this->offset);

        return [$length, $this->value];
    }

    public static function packField($val)
    {
        return FieldFormatEnum::pack($val,MessageFieldClassEnum::getFormat(static::$id));
    }

    public static function getLength() : int
    {
        return MessageFieldClassEnum::getLength(static::$id);
    }
}