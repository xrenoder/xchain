<?php


abstract class aTransactionA extends aTransactionC
{
    use tTransactionConstructor;

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(      /* overrided */
        TransactionFieldClassEnum::TARGET =>    'targetAddrBin',
    );

    /** @var string  */
    protected $targetAddrBin = null;
    public function setTargetAddrBin(string $val) : self {$this->targetAddrBin = $val; return $this;}
    public function getTargetAddrBin() : string {return $this->targetAddrBin;}


    /**
     * @return string
     */
    public function createRaw()
    {
        $this->rawTransactionA();

        $this->compositeRaw();

        return $this;
    }

    protected function rawTransactionA() : void
    {
        $rawTarget = TargetTransactionField::pack($this, $this->targetAddrBin);

        $this->signedData = $rawTarget;
        $this->raw = $rawTarget;
    }
}