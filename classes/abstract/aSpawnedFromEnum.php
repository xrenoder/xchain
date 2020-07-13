<?php


abstract class aSpawnedFromEnum extends aBase
{
    /** @var string  */
    protected static $enumClass = null; /* override me */
    public function getEnumClass() : string {return static::$enumClass;}
    public static function getStaticEnumClass() : string {return static::$enumClass;}

    protected $type = null;
    public function setType($val) {if ($this->type === null) $this->type = $val; return $this;}
    public function &getType() {return $this->type;}

    /** @var bool  */
    protected $parsingError = false;
    public function isParsingError() : bool {return $this->parsingError;}

    public function getName() : string {return get_class($this);}

    public function setTypeFromEnum() : self
    {
        if (static::$enumClass === null) {
            throw new Exception("Bad code - not defined enumClass");
        }

        /** @var aClassEnum $enumClass */
        $enumClass = static::$enumClass;

        if (($type = $enumClass::getTypeByClassName(get_class($this))) === null) {
            throw new Exception("Bad code - for $enumClass bad type (not found or not exclusive): " . $this->getName());
        }

        $this->setType($type);

        return $this;
    }
}