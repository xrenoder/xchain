<?php


class TransactionRewardsRuleRow extends aDbRow
{
    /** @var string  */
    protected static $table = self::SUMMARY_TABLE;     /* overrided */

    /** @var string  */
    protected $id = self::TRANSACTION_REWARD_RULE; /* overrided */
    protected $idFormat = FieldFormatEnum::NOPACK; /* overrided */

    protected static $canBeReplaced = true;     /* overrided */

    /**
     * 'propertyName' => fieldFormat
     * @var array
     */
    protected static $fields = array(
        'masterPercent' =>  FieldFormatEnum::NOPACK,
        'sidePercent' =>    FieldFormatEnum::NOPACK,
        'proxyPercent' =>   FieldFormatEnum::NOPACK,
        'frontPercent' =>   FieldFormatEnum::NOPACK,
    );

    protected $masterPercent = null;
    public function getMasterPercent() {return $this->masterPercent;}

    public function setMasterPercent($val, $needSave = true) : self
    {
        if ($val !== $this->masterPercent) {
            $this->masterPercent = $val;
            $this->saveIfNeed($needSave);
        }

        return $this;
    }

    protected $sidePercent = null;
    public function getSidePercent() {return $this->sidePercent;}

    public function setSidePercent($val, $needSave = true) : self
    {
        if ($val !== $this->sidePercent) {
            $this->sidePercent = $val;
            $this->saveIfNeed($needSave);
        }

        return $this;
    }

    protected $proxyPercent = null;
    public function getProxyPercent() {return $this->proxyPercent;}

    public function setProxyPercent($val, $needSave = true) : self
    {
        if ($val !== $this->proxyPercent) {
            $this->proxyPercent = $val;
            $this->saveIfNeed($needSave);
        }

        return $this;
    }

    protected $frontPercent = null;
    public function getFrontPercent() {return $this->frontPercent;}

    public function setFrontPercent($val, $needSave = true) : self
    {
        if ($val !== $this->frontPercent) {
            $this->frontPercent = $val;
            $this->saveIfNeed($needSave);
        }

        return $this;
    }
}