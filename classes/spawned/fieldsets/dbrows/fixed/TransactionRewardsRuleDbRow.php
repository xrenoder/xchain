<?php


class TransactionRewardsRuleDbRow extends aFixedIdDbRow
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
    public function setMasterPercent(int &$val) : self {return $this->setNewValue($this->masterPercent, $val);}
    public function &getMasterPercent() {return $this->masterPercent;}

    /** @var int  */
    protected $sidePercent = null;
    public function setSidePercent(int &$val) : self {return $this->setNewValue($this->sidePercent, $val);}
    public function &getSidePercent() {return $this->sidePercent;}

    /** @var int  */
    protected $proxyPercent = null;
    public function setProxyPercent(int &$val) : self {return $this->setNewValue($this->proxyPercent, $val);}
    public function &getProxyPercent() {return $this->proxyPercent;}

    /** @var int  */
    protected $frontPercent = null;
    public function setFrontPercent(int &$val) : self {return $this->setNewValue($this->frontPercent, $val);}
    public function &getFrontPercent() {return $this->frontPercent;}
}