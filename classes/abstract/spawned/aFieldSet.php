<?php


abstract class aFieldSet extends aSpawnedFromEnum
{
    /** @var string  */
    protected $fieldClass = null; /* override me */

    /** @var string */
    protected $raw = null;
    public function setRaw(string $val) : self {$this->raw = $val; $this->rawLength = strlen($val); return $this;}

    /** @var int  */
    protected $rawLength = null;
    public function getRawLength() : ?int {return $this->rawLength;}

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(); /* override me */

    protected $fields = array();
    public function getFields() : array {return $this->fields;}

    /** @var aField  */
    protected $field = null;

    /** @var int  */
    protected $fieldPointer = 0;  /* can be overrided */    // first fieldId prepared inside FieldSet-object (field 'Length')

    /** @var int  */
    protected $fieldOffset = 0;
    public function setFieldOffset(int $val) : self {$this->fieldOffset = $val; return $this;}

    abstract public function createRaw() : string;

    protected function __construct(aBase $parent)
    {
        parent::__construct($parent);
    }

    public static function spawn(aBase $parent, $id) : self
    {
        if (static::$enumClass === null) {
            throw new Exception("Bad code - not defined enumClass");
        }

        /** @var aClassEnum $enumClass */
        $enumClass = static::$enumClass;

        /** @var aFieldSet $className */
        if ($className = $enumClass::getClassName($id)) {
            return $className::create($parent);
        }

        throw new Exception("Bad code - cannot spawn class from $enumClass for ID " . $id);
    }

    protected function spawnField(int $fieldId) : aField
    {
        if ($this->fieldClass === null) {
            throw new Exception("Bad code - not defined fieldClass");
        }

        /** @var aField $fieldClass */
        $fieldClass = $this->fieldClass;

        return $fieldClass::spawn($this, $fieldId, $this->fieldOffset);
    }

    protected function getLengthForLast() : int
    {
        return $this->rawLength - $this->fieldOffset;
    }

    public function parseRawString() : void
    {
        foreach ($this->fields as $fieldId => $property) {
            if ($this->fieldPointer > $fieldId) {
                continue;
            }

            if (!$this->prepareField($fieldId, $property)) {
// if field cannot be prepared - break  (not 'return false'), may be all formats was prepared
                break;
            }
        }
    }

    protected function prepareField(int $fieldId, string $property) : bool
    {
        if ($this->$property !== null) return true;

        $field = $this->field;

        if ($field === null) {
            $this->field = $this->spawnField($fieldId);
            $field = $this->field;

            if ($field->isLast()) {
                $field->setLength($this->getLengthForLast());
            }
        }

        if ($this->rawLength >= $field->getParsingPoint()) {
            $this->$property = $field->unpack($this->raw);

            if ($this->$property === null) {
                if ($field->getLength() === null) {  // unpack maxLength or maxValue or fixLength error
                    return false;
                }

                $this->dbg("Prepare field " . $field->getName() . ": field length = " . $field->getLength());
                return $this->prepareField($fieldId, $property);
            }

            $this->postPrepareField($fieldId, $property);

            $this->dbg("Prepared field " . $field->getName() . ": $property = " . $this->$property);
            $this->dbg(bin2hex($field->getRawFieldLength()) . " " . bin2hex($field->getRawWithoutLength()));

            $result = $field->check();

            $this->fieldOffset += $field->getLength();
            $this->fieldPointer = $fieldId + 1;
            $this->field = null;

            return $result;
        }

        return false;
    }

    protected function postPrepareField(int $fieldId, string $property) : void
    {
        /* nothing to do, can be overrided */
    }
}