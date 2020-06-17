<?php


abstract class aFieldFormat extends aSpawnedFromEnum
{
    protected static $dbgLvl = Logger::DBG_FLD_FMT;

    public function getField() : aField {return $this->getParent();}

    /** @var string  */
    protected static $enumClass = 'FieldFormatClassEnum';

    /** @var int  */
    protected $length = null;
    public function setLength(int $val) : self {$this->length = $val; return $this;}
    public function getLength() : ?int {return $this->length;}

    /** @var int  */
    protected $lengthFormatId = null;
    public function setLengthFormatId(?int $val) : self {$this->lengthFormatId = $val; return $this;}

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

    public static function spawn(aField $parent, int $id, int $offset = 0) : self
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
        $this->packDataCommon($data);

        return pack(FieldFormatClassEnum::getPackFormat($this->id), $data);
    }

    protected function packDataCommon($data) : void
    {
        $maxValue = FieldFormatClassEnum::getMaxValue($this->id);

        if ($maxValue && $data > $maxValue) {
            throw new Exception("Bad value of " . $this->getName() . ": $data more than $maxValue");
        }
    }

    protected function unpackRawTransform()
    {
        $this->value = unpack(FieldFormatClassEnum::getPackFormat($this->id), $this->rawWithoutLength)[1];

        return $this->unpackRawCommon();
    }

    protected function unpackRawCommon()
    {
        $maxValue = FieldFormatClassEnum::getMaxValue($this->id);

        if ($maxValue && $this->value > $maxValue) {
            $this->dbg("Bad unpack value of " . $this->getName() . ": $this->value more than $maxValue");

            $this->rawWithoutLength = null;
            $this->length = null;
            $this->value = null;
        }

        return $this->value;
    }
}