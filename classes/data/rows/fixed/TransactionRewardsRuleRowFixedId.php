<?php


class TransactionRewardsRuleRowFixedId extends aFixedIdDbRow
{
    /**
     * 'propertyName' => fieldFormat
     * @var array
     */
    protected static $fieldSet = array(
        'masterPercent' =>  DbFieldClassEnum::ASIS,
        'sidePercent' =>    DbFieldClassEnum::ASIS,
        'proxyPercent' =>   DbFieldClassEnum::ASIS,
        'frontPercent' =>   DbFieldClassEnum::ASIS,
    );

    protected $masterPercent = null;
    public function setMasterPercent($val, $needSave = true) : self {return $this->setNewValue($this->masterPercent, $val, $needSave);}
    public function getMasterPercent() {return $this->masterPercent;}

    protected $sidePercent = null;
    public function setSidePercent($val, $needSave = true) : self {return $this->setNewValue($this->sidePercent, $val, $needSave);}
    public function getSidePercent() {return $this->sidePercent;}

    protected $proxyPercent = null;
    public function setProxyPercent($val, $needSave = true) : self {return $this->setNewValue($this->proxyPercent, $val, $needSave);}
    public function getProxyPercent() {return $this->proxyPercent;}

    protected $frontPercent = null;
    public function setFrontPercent($val, $needSave = true) : self {return $this->setNewValue($this->frontPercent, $val, $needSave);}
    public function getFrontPercent() {return $this->frontPercent;}
}