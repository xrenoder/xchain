<?php
/**
 * Base for storable objects classes (transactions, blocks, settings etc)
 */
abstract class aDbRow extends aFieldSet implements constDbTables, constDbRowIds
{
    protected static $dbgLvl = Logger::DBG_ROW;

    /** @var string  */
    protected static $table = null;     /* override me */

    protected $id = null; /* can be overrided */

    protected $idFormat = DbFieldClassEnum::ASIS; /* can be overrided */

    /** @var aField  */
    private $internalId = null;

    protected static $canBeReplaced = null;     /* override me */

    protected $rawString = null;
    public function getRawString() : ?string {return $this->rawString;}

    /**
     * fieldId => 'propertyName'
     * @var array
     */
    protected static $fieldSet = array(
        DbFieldClassEnum::ASIS => 'value',
    );

    protected $isChanged = false;

    protected $value = null;   /* override me with default value or null */
    public function setValue($val, $needSave = true) : self {return $this->setNewValue($this->value, $val, $needSave);}
    public function getValue() {return $this->value;}

    protected function spawnField(int $fieldId) : aField
    {
        return DbField::spawn($this, $fieldId, $this->fieldOffset);
    }

    protected function __construct(aBase $parent)
    {
        parent::__construct($parent);
        $this->fields = array_replace($this->fields, self::$fieldSet);
        $this->name = get_class($this);
    }

    public static function create(aLocator $locator, $id = null)
    {
        $me = new static($locator);

        if ($id !== null) {
            $me->setId($id);
        } else {
            $me->setInternalId();
        }

        $me->load();

        return $me;
    }

    public function setId($val) : self
    {
        if ($this->id === null) {
            $this->id = $val;
            $this->setInternalId();
        } else {
            throw new Exception( get_class($this) .  ": cannot change id $this->id to $val");
        }

        return $this;
    }

    public function setInternalId() : self
    {
        /** @var DbField $fieldClassName */
        $fieldClassName = DbFieldClassEnum::getItem($this->idFormat);
        $this->internalId = $fieldClassName::pack($this, $this->id, $this->idFormat);

        return $this;
    }

    public function check() : bool
    {
        return $this->getLocator()->getDba()->check(static::$table, $this->internalId);
    }

    public function load() : self
    {
        $this->rawString = $this->getLocator()->getDba()->fetch(static::$table, $this->internalId);
        $this->rawStringLen = strlen($this->rawString);

        if ($this->rawString !== null) {
            $this->parseRawString();
            $this->dbg(get_class($this) . " loaded");
        } else {
            $this->dbg(get_class($this) . " loaded: NULL");
        }

        return $this;
    }

    public function save(bool $replace = false) : self
    {
        if (!$this->isChanged) {
            return $this;
        }

        $this->packFields();

        if ($this->rawString === null) {
            return $this;
        }

        if ($replace && $this->check()) {
            $this->getLocator()->getDba()->update(static::$table, $this->internalId, $this->rawString);
        } else {
            $this->getLocator()->getDba()->insert(static::$table, $this->internalId, $this->rawString);
        }

        $this->dbg(get_class($this) . " saved");

        $this->isChanged = false;

        return $this;
    }

    protected function setNewValue(&$oldVal, $newVal, $needSave) : self
    {
        if ($newVal !== $oldVal) {
            $oldVal = $newVal;
            $this->saveIfNeed($needSave);
        }

        return $this;
    }

    protected function saveIfNeed(bool $needSave) : self
    {
        $this->isChanged = true;

        if ($needSave) {
            $this->save(static::$canBeReplaced);
        }

        return $this;
    }

    private function packFields() : self
    {
        $this->rawString = '';

        foreach($this->fields as $fieldId => $property) {
            if ($this->$property === null) {
                $this->rawString = null;
                return $this;
            }

            /** @var DbField $fieldClassName */
            $fieldClassName = DbFieldClassEnum::getItem($fieldId);
            $this->rawString .= $fieldClassName::pack($this, $this->$property);
        }

        return $this;
    }
}