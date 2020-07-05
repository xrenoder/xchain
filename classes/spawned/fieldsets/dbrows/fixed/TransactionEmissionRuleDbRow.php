<?php


class TransactionEmissionRuleDbRow extends aFixedIdDbRow
{
    /* 'property' => '[fieldType, false or object method]' or 'formatType' */
    protected static $fieldSet = array(
        'bottomLimit' =>        FieldFormatClassEnum::UBIG,
        'emissionPercent' =>    FieldFormatClassEnum::PERCENT,
    );

    /** @var int  */
    protected $bottomLimit = null;
    public function setBottomLimit(int &$val) : self {return $this->setNewValue($this->bottomLimit, $val);}
    public function &getBottomLimit() : int {return $this->bottomLimit;}

    /** @var int  */
    protected $emissionPercent = null;
    public function setEmissionPercent(int &$val) : self {return $this->setNewValue($this->emissionPercent, $val);}
    public function &getEmissionPercent() : int {return $this->emissionPercent;}
}