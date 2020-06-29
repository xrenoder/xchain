<?php


abstract class aDbRow extends aFieldSet
{
    /** @var string  */
    protected $fieldClass = 'aDbField'; /* overrided */

    /** @var string  */
    protected $table = null;

    /** @var int  */
    protected $idFormatType = null; /* can be overrided */

    protected $canBeReplaced = null;     /* override me */

    protected $internalId = null;

    /** @var bool  */
    protected $isChanged = false;

    protected function __construct(aBase $parent)
    {
        parent::__construct($parent);
        $this->fields = array_replace($this->fields, static::$fieldSet);
    }

    public function setType($val) : aFieldSet
    {
        if ($this->table === null) {
            throw new Exception($this->getName() . " Bad code - table must be defined");
        }

        if ($this->idFormatType === null) {
            throw new Exception($this->getName() . " Bad code - idFormatType must be defined");
        }

        $this->type = $val;
        $this->internalId = $this->simplePack($this->idFormatType, $this->type);

        return $this;
    }

    public function check() : bool
    {
        return $this->getLocator()->getDba()->check($this->table, $this->internalId);
    }

    public function load() : self
    {
        $this->setRaw($this->getLocator()->getDba()->fetch($this->table, $this->internalId));

        if ($this->raw !== null) {
            $this->parseRaw();

            if ($this->parsingError) {
                throw new Exception($this->getName() . " cannot be parsed");
            }

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

        $this->createRaw();

        if ($this->raw === null) {
            return $this;
        }

        if ($this->check()) {
            if ($replace) {
                $this->getLocator()->getDba()->update($this->table, $this->internalId, $this->raw);
            } else {
                throw new Exception($this->getName() . " must be replaced, but not have permission");
            }

        } else {
            $this->getLocator()->getDba()->insert($this->table, $this->internalId, $this->raw);
        }

        $this->dbg(get_class($this) . " saved");

        $this->isChanged = false;

        return $this;
    }

    protected function setNewValue(&$oldVal, &$newVal, bool $needSave) : self
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
            $this->save($this->canBeReplaced);
        }

        return $this;
    }
}