<?php


abstract class aSimpleFixedIdDbRow extends aFixedIdDbRow
{
    /* 'property' => '[fieldType, false or object method]' or 'formatType' */
    protected static $fieldSet = array(
        'value' => FieldFormatClassEnum::ASIS,
    );

    protected $value = null;
    public function setValue(&$val, $needSave = true) : self {return $this->setNewValue($this->value, $val, $needSave);}
    public function &getValue() {return $this->value;}
}