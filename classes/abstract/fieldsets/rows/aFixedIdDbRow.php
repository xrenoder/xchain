<?php

abstract class aFixedIdDbRow extends aDbRow
{
    protected static $dbgLvl = Logger::DBG_DB_FROW;

    /** @var string  */
    protected static $enumClass = 'FixedIdDbRowClassEnum'; /* overrided */

    protected $idFormat = DbFieldClassEnum::ASIS; /* can be overrided */

    protected $canBeReplaced = true;     /* overrided */

    private static function create(aBase $parent) : self
    {
        $me = new static($parent);

        $me
            ->setIdFromEnum()
            ->load();

        return $me;
    }

    public function setId($val) : self
    {
        $this->id = $val;
        $this->setInternalId();

        if ($this->table === null) {
            $table = FixedIdDbRowClassEnum::getTable($this->id);

            if ($table !== null) {
                $this->table = FixedIdDbRowClassEnum::getTable($this->id);
                return $this;
            }
        } else {
            return $this;
        }

        throw new Exception($this->getName() . " Bad code - table must be defined for fixed-ID row in FixedDbRowClassEnum (for ID $this->id)");
    }
}