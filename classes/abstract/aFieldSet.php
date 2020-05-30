<?php


abstract class aFieldSet extends aBase
{
    /** @var string  */
    protected $enumClass = null; /* override me */
    public function getEnumClass() : string {return $this->enumClass;}

    protected $id = null;
    public function setId($val) : self {$this->id = $val; return $this;}
    public function getId() {return $this->id;}

    public function getName() : string {return get_class($this);}

    /** @var string */
    protected $rawString;

    /** @var int  */
    protected $rawStringLen = null;
    public function getRawStringLen() : ?int {return $this->rawStringLen;}

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

    abstract protected function spawnField(int $fieldId) : aField;

    protected function __construct(aBase $parent)
    {
        parent::__construct($parent);
    }

    protected function getLengthForLast() : int
    {
        return $this->rawStringLen - $this->fieldOffset;
    }

    protected function parseRawString() : void
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

        if ($this->rawStringLen >= $field->getParsingPoint()) {
            $this->$property = $field->unpack($this->rawString);

            if ($this->$property === null) {
                $this->dbg("Prepare field " . $field->getName() . ": field length = " . $field->getLength());
                return $this->prepareField($fieldId, $property);
            }

            $this->dbg("Prepared field " . $field->getName() . ": $property = " . $this->$property);
            $this->dbg(bin2hex($field->getRawWithoutLength()));

            $result = $field->check();

            $this->fieldOffset += $field->getLength();
            $this->fieldPointer = $fieldId + 1;
            $this->field = null;

            return $result;
        }

        return false;
    }
}