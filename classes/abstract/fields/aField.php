<?php


abstract class aField extends aBase
{
    /** @var int  */
    protected $id = null; /* override me */
    public function setId(int $val) : self {if ($this->id === null) $this->id = $val; return $this;}
    public function getId() : ?int {return $this->id;}

    /** @var string */
    protected $name;
    abstract public function setName() : aField;
    public function getName() : string {return $this->name;}

    /** @var aFieldFormat  */
    protected $format = null;
    public function setFormat(int $offset) : self {$this->format = aFieldFormat::spawn($this, $this->id, $offset); return $this;}
    public function getFormat() : aFieldFormat {return $this->format;}

    public function getParsingPoint() : int {return $this->format->getOffset() + $this->format->getLength();}

    public function isLast() : bool {return $this->format->isLast();}

    public function setLength(int $val) : self {$this->format->setLength($val); return $this;}
    public function getLength() : int {return $this->format->getLength();}

    public function getRaw() : string {return $this->format->getRaw();}

    public function getValue() {return $this->format->getValue();}

    public static function create(aBase $parent, int $offset = 0) : self
    {
        $me = new static($parent);

        return $me->fillMe($offset);
    }

    public function fillMe(int $offset = 0) : self
    {
        if ($this->id === null || $this->format !== null) {
            return $this;
        }

        $this
            ->setFormat($offset)
            ->setName();

        $this->dbg($this->name .  " object created (offset $offset)");

        return $this;
    }

    public function unpack(string $data)
    {
        return $this->format->unpackField($data);
    }

    public static function pack($parent, $val, int $fieldId = null) : string
    {
        $field = static::create($parent);

        if ($field->getId() === null) {
            if ($fieldId !== null) {
                $field->setId($fieldId);
                $field->fillMe();
            } else {
                throw new Exception( get_class($field) .  ": cannot create without field ID");
            }
        }

        $result = $field->getFormat()->packField($val);

        unset($field);

        return $result;
    }

    public function check(): bool
    {
        return true;
    }
}