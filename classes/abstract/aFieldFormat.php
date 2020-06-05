<?php


abstract class aFieldFormat extends aSpawnedFromEnum
{
    protected static $dbgLvl = Logger::DBG_FLD_FMT;

    /** @var string  */
    protected static $enumClass = 'FieldFormatClassEnum';

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
    public function getRawFieldLength() : string {return $this->rawFieldLength;}

    public function getRawWithLength() : string {return $this->rawFieldLength . $this->rawWithoutLength;}

    protected $value = null;
    public function getValue() {return $this->value;}

    protected static function create(aField $parent, int $offset = 0) : ?self
    {
        $me = new static($parent);

        $me
            ->setIdFromEnum()
            ->setLength(FieldFormatClassEnum::getLength($me->getId()))
            ->setLengthFormatId(FieldFormatClassEnum::getLengthFormatId($me->getId()))
            ->setIsLast()
            ->setOffset($offset);

        $me->dbg($me->getName() .  " object created");

        return $me;
    }

    public static function spawn(aField $parent, string $id, int $offset = 0) : self
    {
        /** @var aFieldFormat $className */

        if ($className = FieldFormatClassEnum::getClassName($id)) {
            return $className::create($parent, $offset);
        }

        throw new Exception("Bad code - cannot spawn class from FieldFormatClassEnum for ID " . $id);
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