<?php


class aBaseEnum extends aBase
{
    /** @var string  */
    protected $enumClass = null; /* override me */
    public function getEnumClass() : string {return $this->enumClass;}

    protected $id = null;
    public function setId($val) {if ($this->id === null) $this->id = $val; return $this;}
    public function getId() {return $this->id;}

    public function getName() : string {return get_class($this);}
}