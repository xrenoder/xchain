<?php


abstract class aFieldFormat extends aSpawnedFromEnum
{
    protected static $dbgLvl = Logger::DBG_FLD_FMT;

    /** @var string  */
    protected static $enumClass = 'FieldFormatClassEnum';

    /** @var int  */
    protected $length = null;
    public function setLength(int $val) : self {$this->length = $val; return $this;}
    public function getLength() : ?int {return $this->length;}

    /** @var int  */
    protected $lengthFormatType = null;
    public function setLengthFormatType(?int $val) : self {$this->lengthFormatType = $val; return $this;}

    /** @var bool */
    protected $isLast;
    public function setIsLast() : self {$this->isLast = FieldFormatClassEnum::isLast($this->type); return $this;}
    public function isLast() : bool {return $this->isLast;}

    /** @var int  */
    protected $offset = null;
    public function setOffset(int $val) : self {$this->offset = $val; return $this;}
    public function getOffset() : int {return $this->offset;}

    /** @var string  */
    protected $rawWithoutLength = null;
    public function &getRawWithoutLength() : string {return $this->rawWithoutLength;}

    /** @var string  */
    protected $rawFieldLength = '';
    public function &getRawFieldLength() : string {return $this->rawFieldLength;}

    /** @var string  */
    protected $rawWithLength = null;
    public function &getRawWithLength() : string {return $this->rawWithLength;}

    public function setRaw(string $val) : self {$this->rawWithoutLength = $val; $this->rawWithLength = $this->rawFieldLength . $this->rawWithoutLength; return $this;}
    public function unsetRaw() : self {$this->rawWithoutLength = null; $this->rawWithLength = null; return $this;}

    protected $value = null;
    public function &getValue() {return $this->value;}

    protected static function create(aBase $parent, int $offset = 0) : ?self
    {
        $me = new static($parent);

        $me
            ->setTypeFromEnum()
            ->setLength(FieldFormatClassEnum::getLength($me->getType()))
            ->setLengthFormatType(FieldFormatClassEnum::getLengthFormatId($me->getType()))
            ->setIsLast()
            ->setOffset($offset);

        $me->dbg($me->getName() .  " object created");

        return $me;
    }

    public static function spawn(aBase $parent, int $type, int $offset = 0) : self
    {
        /** @var aFieldFormat $className */

        if ($className = FieldFormatClassEnum::getClassName($type)) {
            return $className::create($parent, $offset);
        }

        throw new Exception("Bad code - cannot spawn class from FieldFormatClassEnum for type " . $type);
    }

    /* can be overrided */
    public function &packField(&$data) : string
    {
        if ($data === null) {
            throw new Exception($this->getName() . " Bad coding: packed data must be not null");
        }

        $result = $this->packDataTransform($data);

        $this->dbg($this->getName() . ': ' . $data .  " packed to \n" . bin2hex($result));

        return $result;
    }

    public function &unpackField(string &$fieldRaw)
    {
        $this->setRaw(substr($fieldRaw, $this->offset, $this->length));

        return $this->unpackRawTransform();
    }

    protected function &packDataTransform(&$data) : string
    {
        $this->packDataCommon($data);

        $result = pack(FieldFormatClassEnum::getPackFormat($this->type), $data);

        return $result;
    }

    protected function packDataCommon(&$data) : void
    {
        $maxValue = FieldFormatClassEnum::getMaxValue($this->type);

        if ($maxValue && $data > $maxValue) {
            throw new Exception("Bad value of " . $this->getName() . ": $data more than $maxValue");
        }
    }

    protected function &unpackRawTransform()
    {
        $this->value = unpack(FieldFormatClassEnum::getPackFormat($this->type), $this->rawWithoutLength)[1];
        $this->unpackRawCommon();

        return $this->value;
    }

    protected function unpackRawCommon() : void
    {
        $maxValue = FieldFormatClassEnum::getMaxValue($this->type);

        if ($maxValue && $this->value > $maxValue) {
            $this->err("Bad unpack value of " . $this->getName() . ": $this->value more than $maxValue");

            $this->unsetRaw();
            $this->length = null;
            $this->value = null;
        }
    }
}