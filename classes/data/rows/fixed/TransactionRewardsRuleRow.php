<?php


class TransactionRewardsRuleRow extends aFixedIdDbRow
{
    /* 'property' => '[fieldType, false or object method]' or 'formatType' */
    protected static $fieldSet = array(
        'masterPercent' =>  FieldFormatClassEnum::PERCENT,
        'sidePercent' =>    FieldFormatClassEnum::PERCENT,
        'proxyPercent' =>   FieldFormatClassEnum::PERCENT,
        'frontPercent' =>   FieldFormatClassEnum::PERCENT,
    );

    /** @var int  */
    protected $masterPercent = null;
    public function setMasterPercent(int &$val, $needSave = true) : self {return $this->setNewValue($this->masterPercent, $val, $needSave);}
    public function &getMasterPercent() {return $this->masterPercent;}

    /** @var int  */
    protected $sidePercent = null;
    public function setSidePercent(int &$val, $needSave = true) : self {return $this->setNewValue($this->sidePercent, $val, $needSave);}
    public function &getSidePercent() {return $this->sidePercent;}

    /** @var int  */
    protected $proxyPercent = null;
    public function setProxyPercent(int &$val, $needSave = true) : self {return $this->setNewValue($this->proxyPercent, $val, $needSave);}
    public function &getProxyPercent() {return $this->proxyPercent;}

    /** @var int  */
    protected $frontPercent = null;
    public function setFrontPercent(int &$val, $needSave = true) : self {return $this->setNewValue($this->frontPercent, $val, $needSave);}
    public function &getFrontPercent() {return $this->frontPercent;}
}