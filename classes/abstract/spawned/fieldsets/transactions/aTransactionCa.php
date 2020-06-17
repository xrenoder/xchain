<?php


abstract class aTransactionCa extends aTransactionC
{
    use tTransactionConstructor;

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(      /* overrided */
        TransactionFieldClassEnum::AMOUNT =>    'amount',
    );

    /** @var string  */
    protected $amount = null;
    public function setAmount(int $val) : self {$this->amount = $val; return $this;}
    public function getAmount() : string {return $this->amount;}


    /**
     * @return string
     */
    public function createRaw()
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