<?php


abstract class aTransactionA extends aTransactionC
{
    use tTransactionConstructor;

    /* 'property' => '[fieldType, isObject]' or 'formatType' */
    protected static $fieldSet = array(      /* overrided */
        'targetAddress' => [TransactionFieldClassEnum::TARGET, 'getAddressBin'],
    );

    /** @var Address  */
    protected $targetAddress = null;
    public function setTargetAddress(string $val) : self {$this->targetAddress = $val; return $this;}
    public function getTargetAddress() : Address {return $this->targetAddress;}


    /**
     * @return string
     */
    public function createRaw() : aFieldSet
    {
        $this->rawTransactionA();

        $this->compositeRaw();

        return $this;
    }

    protected function rawTransactionA() : void
    {
        $rawTarget = TargetTransactionField::pack($this, $this->targetAddress->getAddressBin());

        $this->signedData = $rawTarget;
        $this->raw = $rawTarget;
    }
}