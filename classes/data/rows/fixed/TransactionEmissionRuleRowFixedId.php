<?php


class TransactionEmissionRuleRowFixedId extends aFixedIdDbRow
{
    /**
     * 'propertyName' => fieldFormat
     * @var array
     */
    protected static $fieldSet = array(
        'bottomLimit' =>        DbFieldClassEnum::ASIS,
        'emissionPercent' =>    DbFieldClassEnum::ASIS,
    );

    protected $bottomLimit = null;
    public function setBottomLimit($val, $needSave = true) : self {return $this->setNewValue($this->bottomLimit, $val, $needSave);}
    public function getBottomLimit() {return $this->bottomLimit;}


    protected $emissionPercent = null;
    public function setEmissionPercent($val, $needSave = true) : self {return $this->setNewValue($this->emissionPercent, $val, $needSave);}
    public function getEmissionPercent() {return $this->emissionPercent;}
}