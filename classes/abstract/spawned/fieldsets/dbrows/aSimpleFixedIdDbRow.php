<?php


abstract class aSimpleFixedIdDbRow extends aFixedIdDbRow
{
    /* 'property' => '[fieldType, false or object method]' or 'formatType' */
    protected static $fieldSet = array(
        'value' => FieldFormatClassEnum::ASIS,
    );

    protected $value = null;
    public function setValue(&$val) : self {return $this->setNewValue($this->value, $val);}
    public function &getValue() {return $this->value;}
}