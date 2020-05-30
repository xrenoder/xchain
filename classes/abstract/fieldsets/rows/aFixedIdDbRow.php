<?php
/**
 * Base for storable objects classes (transactions, blocks, settings etc)
 */
abstract class aFixedIdDbRow extends aDbRow
{
    protected static $dbgLvl = Logger::DBG_DB_FROW;

    /** @var string  */
    protected $enumClass = 'FixedIdDbRowClassEnum'; /* overrided */

    protected $idFormat = DbFieldClassEnum::ASIS; /* can be overrided */

    protected $canBeReplaced = true;     /* overrided */

    private static function create(aLocator $locator) : self
    {
        $me = new static($locator);

        /** @var aClassEnum $enumClass */
        $enumClass = $me->getEnumClass();

        if (($id = $enumClass::getIdByClassName(get_class($me))) === null) {
            throw new Exception("Bad code - unknown ID (not found or not exclusive) for class " . $me->getName());
        }

        $me
            ->setId($id)
            ->load();

        return $me;
    }

    public static function spawn(aLocator $parent, string $id) : self
    {
        /** @var aFixedIdDbRow $className */
        if ($className = FixedIdDbRowClassEnum::getClassName($id)) {
            return $className::create($parent);
        }

        throw new Exception("Bad code - unknown fixed-ID row class for ID " . $id);
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