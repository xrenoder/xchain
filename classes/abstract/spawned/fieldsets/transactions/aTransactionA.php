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
        if ($this->targetAddress === null) {
            throw new Exception($this->getName() . " Bad code - target address cannot be null");
        }

        if (!($this->targetAddress instanceof Address)) {
            throw new Exception($this->getName() . " Bad code - target address must be insatance of Address");
        }

        $this->raw = TargetTransactionField::pack($this, $this->targetAddress->getAddressBin());
    }
}