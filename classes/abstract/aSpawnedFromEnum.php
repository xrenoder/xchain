<?php


class aSpawnedFromEnum extends aBase
{
    /** @var string  */
    protected static $enumClass = null; /* override me */
    public function getEnumClass() : string {return static::$enumClass;}
    public static function getStaticEnumClass() : string {return static::$enumClass;}

    protected $id = null;
    public function setId($val) {if ($this->id === null) $this->id = $val; return $this;}
    public function getId() {return $this->id;}

    public function getName() : string {return get_class($this);}

    public function setIdFromEnum() : self
    {
        if (static::$enumClass === null) {
            throw new Exception("Bad code - not defined enumClass");
        }

        /** @var aClassEnum $enumClass */
        $enumClass = static::$enumClass;

        if (($id = $enumClass::getIdByClassName(get_class($this))) === null) {
            throw new Exception("Bad code - for $enumClass bad ID (not found or not exclusive): " . $this->getName());
        }

        $this->setId($id);

        return $this;
    }
}