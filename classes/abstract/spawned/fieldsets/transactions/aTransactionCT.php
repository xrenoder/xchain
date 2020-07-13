<?php


abstract class aTransactionCT extends aTransaction
{
    /* 'property' => '[fieldType, isObject]' or 'formatType' */
    protected static $fieldSet = array(      /* overrided */
        'data' => [TransactionFieldClassEnum::TINY_DATA, 'getRaw'],
    );

    public function createRaw() : aFieldSet
    {
        $this->rawTransactionCT();

        $this->compositeRaw();

        return $this;
    }

    protected function rawTransactionCT() : void
    {
        if ($this->data === null) {
            throw new Exception($this->getName() . " Bad code - data cannot be null");
        }

        if (!($this->data instanceof aTransactionData)) {
            throw new Exception($this->getName() . " Bad code - data must be insatance of aTransactionData");
        }

        $this->raw = TinyDataTransactionField::pack($this, $this->data->getRaw());
    }
}