<?php


abstract class aFieldFormat extends aBase
{
    protected static $dbgLvl = Logger::DBG_FLD_FMT;

    /** @var string  */
    protected $enumClass = 'FieldFormatClassEnum';
    public function getEnumClass() : string {return $this->enumClass;}

    /** @var string  */
    protected $id;
    public function setId(string $val) : self {if ($this->id === null) $this->id = $val; return $this;}
    public function getId() : string {return $this->id;}

    public function getName() : string {return get_class($this);}

    /** @var int  */
    protected $length = null;
    public function setLength(int $val) : self {$this->length = $val; return $this;}
    public function getLength() : int {return $this->length;}

    /** @var string  */
    protected $lengthFormatId = null;
    public function setLengthFormatId(?string $val) : self {$this->lengthFormatId = $val; return $this;}

    /** @var bool */
    protected $isLast;
    public function setIsLast() : self {$this->isLast = FieldFormatClassEnum::isLast($this->id); return $this;}
    public function isLast() : bool {return $this->isLast;}

    /** @var int  */
    protected $offset = null;
    public function setOffset(int $val) : self {$this->offset = $val; return $this;}
    public function getOffset() : int {return $this->offset;}

    protected $rawWithoutLength = null;
    public function getRawWithoutLength() : string {return $this->rawWithoutLength;}

    protected $rawFieldLength = '';
    public function getRawWithLength() : string {return $this->rawFieldLength . $this->rawWithoutLength;}

    protected $value = null;
    public function getValue() {return $this->value;}

    protected static function create(aBase $parent, int $offset = 0) : ?self
    {
        $me = new static($parent);

        /** @var aClassEnum $enumClass */
        $enumClass = $me->getEnumClass();

        if (($id = $enumClass::getIdByClassName(get_class($me))) === null) {
            throw new Exception("Bad code - unknown ID (not found or not exclusive) for field format class " . $me->getName());
        }

        $me
            ->setId($id)
            ->setLength(FieldFormatClassEnum::getLength($me->getId()))
            ->setLengthFormatId(FieldFormatClassEnum::getLengthFormatId($me->getId()))
            ->setIsLast()
            ->setOffset($offset);

        $me->dbg($me->getName() .  " object created");

        return $me;
    }

    public static function spawn(aBase $parent, string $id, int $offset = 0) : self
    {
        /** @var aFieldFormat $className */

        if ($className = FieldFormatClassEnum::getClassName($id)) {
            return $className::create($parent, $offset);
        }

        throw new Exception("Bad code - unknown field format class for ID " . $id);
    }

    /* can be overrided */
    public function packField($data) : string
    {
        return $this->packDataTransform($data);
    }

    public function unpackField(string $data)
    {
        $this->rawWithoutLength = substr($data, $this->offset, $this->length);
        return $this->unpackRawTransform();
    }

    protected function packDataTransform($data) : string
    {
        return pack($this->id, $data);
    }

    protected function unpackRawTransform()
    {
        $this->value = unpack($this->id, $this->rawWithoutLength)[1];
        return $this->value;
    }
}