<?php


class TransactionEmissionRuleRow extends aDbRow
{
    /** @var string  */
    protected static $table = self::SUMMARY_TABLE;     /* overrided */

    /** @var string  */
    protected $id = self::TRANSACTION_EMISSION_RULE; /* overrided */

    protected static $canBeReplaced = true;     /* overrided */

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