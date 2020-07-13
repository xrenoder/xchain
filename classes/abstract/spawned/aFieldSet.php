<?php


abstract class aFieldSet extends aSpawnedFromEnum
{
    /** @var string  */
    protected $fieldClass = null; /* override me */

    /** @var string */
    protected $raw = null;
    public function setRaw(string &$val) : self {$this->raw = $val; $this->rawLength = strlen($val); return $this;}
    public function &getRaw() : ?string {if ($this->raw === null) {$this->createRaw();} return $this->raw;}

    /** @var string */
    protected $rawType = null;

    /** @var int  */
    protected $rawLength = null;
    public function &getRawLength() : ?int {return $this->rawLength;}

    /* 'property' => '[fieldType, false or object method]' or 'formatType' */
    protected static $fieldSet = array(); /* override me */

    protected $fields = array();

    /** @var aField  */
    protected $field = null;

    /** @var int  */
    protected $fieldPointer = 0;  /* can be overrided */    // number of first field, prepared inside FieldSet-object

    /** @var int  */
    protected $fieldOffset = 0;
    public function setFieldOffset(int $val) : self {$this->fieldOffset = $val; return $this;}

    protected function __construct(aBase $parent)
    {
        parent::__construct($parent);
    }

    public static function spawn(aBase $parent, $type) : self
    {
        if (static::$enumClass === null) {
            throw new Exception("Bad code - not defined enumClass");
        }

        /** @var aClassEnum $enumClass */
        $enumClass = static::$enumClass;

        /** @var self $className */
        if ($className = $enumClass::getClassName($type)) {
            return $className::create($parent);
        }

        throw new Exception("Bad code - cannot spawn class from $enumClass for type " . $type);
    }

    protected function spawnField(int $fieldType) : aField
    {
        if ($this->fieldClass === null) {
            throw new Exception($this->getName() . " Bad code - not defined fieldClass");
        }

        /** @var aField $fieldClass */
        $fieldClass = $this->fieldClass;

        return $fieldClass::spawn($this, $fieldType, $this->fieldOffset);
    }

    protected function getLengthForLast() : int
    {
        return $this->rawLength - $this->fieldOffset;
    }

    public function parseRaw() : void
    {
        $fieldCounter = -1;

        $this->parsingRawPre();

        foreach ($this->fields as $property => $formatOrField) {
            $fieldCounter++;

            if ($this->fieldPointer > $fieldCounter) {
                continue;
            }

            if ($this->fieldOffset >= $this->rawLength) {
                $this->parsingError = true;
                $this->dbg($this->getName() . " parsing error: $property offset $this->fieldOffset out of raw length $this->rawLength");
                break;
            }

            if (is_array($formatOrField)) {
                $fieldType = $formatOrField[0];

                if (isset($formatOrField[1]) && $formatOrField[1]) {
                    $isObject = true;
                } else {
                    $isObject = false;
                }

                if (!$this->prepareField($fieldType, $property, $isObject)) {
                    break;
                }
            } else {
                if (!$this->prepareFormat($formatOrField, $property)) {
                    break;
                }
            }

            if (!$this->parsingRawInterrupt($property)) {
                break;
            }
        }

        $this->parsingRawPost();
    }

    protected function prepareFormat(int $formatType, string $property) : bool
    {
        $this->$property = $this->simpleUnpack($formatType, $this->raw, $this->fieldOffset);

        if ($this->$property === null) {
            $this->parsingError = true;
            return false;
        }

        $this->fieldOffset += $this->getUnpackedLength();
        $this->fieldPointer++;

        return true;
    }

    protected function prepareField(int $fieldType, string $property, bool $isObject) : bool
    {
        if ($this->field === null) {
            $this->field = $this->spawnField($fieldType);

            if ($this->field->isLast()) {
                $this->field->setLength($this->getLengthForLast());
            }
        }

        if ($this->rawLength >= $this->field->getParsingPoint()) {
            $this->field->unpack($this->raw);

            if ($this->field->getValue() === null) {
                if ($this->field->getLength() === null) {  // unpack maxLength or maxValue or fixLength error
                    $this->parsingError = true;
                    unset($this->field);
                    return false;
                }

                $this->dbg("Prepare field " . $this->field->getName() . " length for $property = " . $this->field->getLength());
                return $this->prepareField($fieldType, $property, $isObject);
            }

            $this->dbg("Prepared field " . $this->field->getName() . " for $property = " . $this->field->getValue());
            $this->dbg(bin2hex($this->field->getRawFieldLength()) . " " . bin2hex($this->field->getRawWithoutLength()));

            $result = $this->field->checkValue();

            if ($result) {
                if (!$isObject) {
                    $this->$property = $this->field->getValue();
                } else {
                    $this->field->setObject();
                    $result = $this->field->checkObject();

                    if ($result) {
                        $this->$property = $this->field->getOblect();
                    }
                }
            }

            if ($result && !$this->field->isParsingError()) {
                $result = $this->field->postPrepare();
            }

            if ($this->field->isParsingError()) {
                $this->parsingError = true;
                unset($this->field);
                return false;
            }

            $this->fieldOffset += $this->field->getLength();
            $this->fieldPointer++;
            unset($this->field);

            return $result;
        }

        return false;
    }

    protected function parsingRawPre() : void
    {

    }

    protected function parsingRawPost() : void
    {

    }

    protected function parsingRawInterrupt(string &$property) : bool
    {
        return true;
    }

    public function createRaw() : aFieldSet
    {
        if ($this->fieldClass === null) {
            throw new Exception($this->getName() . " Bad code - not defined fieldClass");
        }

        /** @var aField $fieldClass */
        $fieldClass = $this->fieldClass;

        /** @var aFieldClassEnum $fieldClassEnum */
        $fieldClassEnum = $fieldClass::getStaticEnumClass();

        $this->creatingRawPre();

        $this->raw = '';

        foreach($this->fields as $property => $formatOrField) {
            $this->creatingRawPreInterrupt($property);

            if ($this->$property === null) {
                throw new Exception($this->getName() . " Bad code - property $property is null");
            }

            if (is_array($formatOrField)) {
                $fieldType = $formatOrField[0];

                if (isset($formatOrField[1]) && $formatOrField[1]) {
                    $objectMethod = $formatOrField[1];

                    if (is_callable(array($this->$property, $objectMethod))) {
                        throw new Exception($this->getName() . " Bad code - $property->$objectMethod() is not callable");
                    }

                    $value = $this->$property->$objectMethod();

                    /** @var aField $fieldClassName */
                    $fieldClassName = $fieldClassEnum::getItem($fieldType);

                    $this->raw .= $fieldClassName::pack($this, $value);
                } else {
                    $this->raw .= $this->simplePack($formatOrField, $this->$property);
                }
            }

            $this->creatingRawPostInterrupt($property);
        }

        $this->rawLength = strlen($this->raw);
        $this->dbg($this->getName() . " raw created ($this->rawLength bytes):\n" . bin2hex($this->raw) . "\n");

        return $this;
    }

    public function creatingRawPre() : void
    {

    }

    public function creatingRawPreInterrupt(string &$property) : void
    {

    }

    public function creatingRawPostInterrupt(string &$property) : void
    {

    }
}