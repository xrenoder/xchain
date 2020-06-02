<?php


abstract class aMultyIdDbRow extends aDbRow
{
    protected static $dbgLvl = Logger::DBG_DB_MROW;

    protected $canBeReplaced = false;     /* overrided */

    public static function create(aLocator $locator, $id) : self
    {
        $me = new static($locator);
        $me
            ->setId($id)
            ->load();

        return $me;
    }

    public function setId($val) : aFieldSet
    {
        if ($this->table === null) {
            throw new Exception($this->getName() . " Bad code - table must be defined for multy-ID row in row classenum (for ID $this->id)");
        }

        $this->id = $val;
        $this->setInternalId();

        return $this;
    }
}