<?php


abstract class aSimpleFixedIdDbRow extends aFixedIdDbRow
{
   /**
     * fieldId => 'propertyName'
     * @var array
     */
    protected static $fieldSet = array(
        DbFieldClassEnum::ASIS => 'value',
    );

    protected $value = null;   /* override me with default value or null */
    public function setValue($val, $needSave = true) : self {return $this->setNewValue($this->value, $val, $needSave);}
    public function getValue() {return $this->value;}
}