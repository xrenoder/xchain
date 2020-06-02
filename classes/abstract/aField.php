<?php


abstract class aField extends aBaseEnum
{
    /** @var aFieldFormat  */
    protected $format = null;
    public function getFormat() : aFieldFormat {return $this->format;}

    public function getParsingPoint() : int {return $this->format->getOffset() + $this->format->getLength();}

    public function isLast() : bool {return $this->format->isLast();}

    public function setLength(int $val) : self {$this->format->setLength($val); return $this;}
    public function getLength() : int {return $this->format->getLength();}

    public function getRawWithoutLength() : string {return $this->format->getRawWithoutLength();}
    public function getRawFieldLength() : string {return $this->format->getRawFieldLength();}
    public function getRawWithLength() : string {return $this->format->getRawWithLength();}

    public function getValue() {return $this->format->getValue();}

    public static function create(aBase $parent, int $offset = 0) : self
    {
        $me = new static($parent);

        /** @var aClassEnum $enumClass */
        $enumClass = $me->getEnumClass();

        if (($id = $enumClass::getIdByClassName(get_class($me))) === null) {
            throw new Exception("Bad code - unknown ID (not found or not exclusive) for field classenum " . $me->getName());
        }

        $me
            ->setId($id)
            ->setFormat($offset);

        $me->dbg($me->getName() .  " object created (offset $offset)");

        return $me;
    }

    public function setFormat(int $offset) : self
    {
        /** @var aFieldClassEnum $enumClass */
        $enumClass = $this->enumClass;
        $formatId = $enumClass::getFormat($this->id);
        $this->format = aFieldFormat::spawn($this, $formatId, $offset);
        return $this;
    }

    public function unpack(string $data)
    {
        return $this->format->unpackField($data);
    }

    public static function pack($parent, $val) : string
    {
        $field = static::create($parent);
        $result = $field->getFormat()->packField($val);
        $field->dbg($val);
        $field->dbg(bin2hex($result));
        return $result;
    }

    public function check(): bool
    {
        return true;
    }
}