<?php


abstract class aTransactionAa extends aTransactionA
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
    public function createRaw() : ?string
    {
        $this->rawTransactionAa();

        return $this->compositeRaw();
    }

    protected function rawTransactionAa() : void
    {
        $this->rawTransactionA();

        $rawAmount = AmountTransactionField::pack($this, $this->amount);

        $this->signedData .= $rawAmount;
        $this->raw .= $rawAmount;
    }

}