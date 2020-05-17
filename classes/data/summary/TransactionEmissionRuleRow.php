<?php


class TransactionEmissionRuleRow extends aDbRow
{
    /** @var string  */
    protected static $table = self::SUMMARY_TABLE;     /* overrided */

    /** @var string  */
    protected $id = self::TRANSACTION_EMISSION_RULE; /* overrided */
    protected $idFormat = FieldFormatEnum::NOPACK; /* overrided */

    protected static $canBeReplaced = true;     /* overrided */

    /**
     * 'propertyName' => fieldFormat
     * @var array
     */
    protected static $fields = array(
        'bottomLimit' =>        FieldFormatEnum::NOPACK,
        'emissionPercent' =>    FieldFormatEnum::NOPACK,
    );

    protected $bottomLimit = null;
    public function getBottomLimit() {return $this->bottomLimit;}

    public function setBottomLimit($val, $needSave = true) : self
    {
        if ($val !== $this->bottomLimit) {
            $this->bottomLimit = $val;
            $this->saveIfNeed($needSave);
        }

        return $this;
    }

    protected $emissionPercent = null;
    public function getEmissionPercent() {return $this->emissionPercent;}

    public function setEmissionPercent($val, $needSave = true) : self
    {
        if ($val !== $this->emissionPercent) {
            $this->emissionPercent = $val;
            $this->saveIfNeed($needSave);
        }

        return $this;
    }
}