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
        $rawAmount = AmountTransactionField::pack($this, $this->amount);

        $this->signedData = $rawAmount;
        $this->raw = $rawAmount;
    }
}