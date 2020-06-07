<?php


abstract class aDbRow extends aFieldSet
{
    /** @var string  */
    protected $fieldClass = 'aDbField'; /* overrided */

    /** @var string  */
    protected $table = null;

    /** @var int  */
    protected $idFormat = null; /* can be overrided */

    protected $canBeReplaced = null;     /* override me */

    /** @var aDbField  */
    protected $internalId = null;

    /** @var bool  */
    protected $isChanged = false;

    protected function __construct(aBase $parent)
    {
        parent::__construct($parent);
        $this->fields = array_replace($this->fields, self::$fieldSet);
    }

    public function setInternalId() : self
    {
        /** @var aDbField $fieldClassName */
        $fieldClassName = DbFieldClassEnum::getItem($this->idFormat);
        $this->internalId = $fieldClassName::pack($this, $this->id);

        return $this;
    }

    public function check() : bool
    {
        return $this->getLocator()->getDba()->check($this->table, $this->internalId);
    }

    public function load() : self
    {
        $this->raw = $this->getLocator()->getDba()->fetch($this->table, $this->internalId);
        $this->rawLength = strlen($this->raw);

        if ($this->raw !== null) {
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

        $this->createRaw();

        if ($this->raw === null) {
            return $this;
        }

        if ($replace && $this->check()) {
            $this->getLocator()->getDba()->update($this->table, $this->internalId, $this->raw);
        } else {
            $this->getLocator()->getDba()->insert($this->table, $this->internalId, $this->raw);
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
            $this->save($this->canBeReplaced);
        }

        return $this;
    }
}