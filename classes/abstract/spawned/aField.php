<?php


abstract class aField extends aSpawnedFromEnum
{
    /** @var string  */
    protected static $parentClass = null; /* override me */

    /** @var aFieldFormat  */
    protected $format = null;
    public function getFormat() : aFieldFormat {return $this->format;}

    public function getParsingPoint() : int {return $this->format->getOffset() + $this->format->getLength();}

    public function isLast() : bool {return $this->format->isLast();}

    public function setLength(int $val) : self {$this->format->setLength($val); return $this;}
    public function getLength() : ?int {return $this->format->getLength();}

    public function &getRawWithoutLength() : string {return $this->format->getRawWithoutLength();}
    public function &getRawFieldLength() : string {return $this->format->getRawFieldLength();}
    public function &getRawWithLength() : string {return $this->format->getRawWithLength();}

    public function &getValue() {return $this->format->getValue();}

    protected $object = null;
    public function getOblect() {return $this->object;}

    public static function create(aBase $parent, int $offset = 0) : self
    {
        $me = new static($parent);

        $me
            ->setTypeFromEnum()
            ->setFormat($offset);

        $me->dbg($me->getName() .  " object created (offset $offset)");

        return $me;
    }

    public static function spawn(aFieldSet $parent, int $type, int $offset) : self
    {
        if (static::$enumClass === null) {
            throw new Exception("Bad code - not defined enumClass");
        }

        if (static::$parentClass === null) {
            throw new Exception("Bad code - not defined parentClass");
        }

        if (!is_a($parent, static::$parentClass, true)) {
            throw new Exception( $parent->getName() . " is not instance of " . static::$parentClass);
        }

        /** @var aFieldClassEnum $enumClass */
        $enumClass = static::$enumClass;

        /** @var aField $className */
        if ($className = $enumClass::getClassName($type)) {
            return $className::create($parent, $offset);
        }

        throw new Exception("Bad code - cannot spawn class from $enumClass for type " . $type);
    }

    public function setFormat(int $offset) : self
    {
        /** @var aFieldClassEnum $enumClass */
        $enumClass = static::$enumClass;
        $formatType = $enumClass::getFormat($this->type);

        $this->format = aFieldFormat::spawn($this, $formatType, $offset);

        return $this;
    }

    public function unpack(string &$data)
    {
        return $this->format->unpackField($data);
    }

    public static function &pack(aBase $parent, &$val) : string
    {
        $field = static::create($parent);
        $result = $field->getFormat()->packField($val);
        unset($field);

        return $result;
    }

    public function checkValue(): bool
    {
        return true;
    }

    public function checkObject(): bool
    {
        return true;
    }

    public function setObject() :  void
    {
        throw new Exception($this->getName() . " Bad code - method 'setObject' not defined");
    }

    public function postPrepare() :  bool
    {
        return true;
    }
}