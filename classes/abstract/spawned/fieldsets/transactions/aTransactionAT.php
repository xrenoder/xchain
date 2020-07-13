<?php


abstract class aTransactionAT extends aTransactionA
{
    use tTransactionConstructor;

    /* 'property' => '[fieldType, isObject]' or 'formatType' */
    protected static $fieldSet = array(      /* overrided */
        'data' => [TransactionFieldClassEnum::TINY_DATA, 'getRaw'],
    );

    public function createRaw() : aFieldSet
    {
        $this->rawTransactionAT();

        $this->compositeRaw();

        return $this;
    }

    protected function rawTransactionAT() : void
    {
        if ($this->data === null) {
            throw new Exception($this->getName() . " Bad code - data cannot be null");
        }

        if (!($this->data instanceof aTransactionData)) {
            throw new Exception($this->getName() . " Bad code - data must be insatance of aTransactionData");
        }

        $this->rawTransactionA();
        $this->raw .= ShortDataTransactionField::pack($this, $this->data->getRaw());
    }
}