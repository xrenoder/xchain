<?php


abstract class aTransactionCa extends aTransactionC
{
    use tTransactionConstructor;

    /* 'property' => '[fieldType, isObject]' or 'formatType' */
    protected static $fieldSet = array(      /* overrided */
        'amount' => [TransactionFieldClassEnum::AMOUNT, false]
    );

    /** @var int  */
    protected $amount = null;
    public function setAmount(int $val) : self {$this->amount = $val; return $this;}
    public function getAmount() : int {return $this->amount;}

    public function createRaw() : aFieldSet
    {
        $this->rawTransactionCa();

        $this->compositeRaw();

        return $this;
    }

    protected function rawTransactionCa() : void
    {
        if ($this->amount === null) {
            throw new Exception($this->getName() . " Bad code - amount cannot be null");
        }

        if ($this->amount <= 0) {
            throw new Exception($this->getName() . " Bad code - amount must be >0");
        }

        $this->raw = AmountTransactionField::pack($this, $this->amount);
    }
}