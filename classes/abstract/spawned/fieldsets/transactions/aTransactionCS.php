<?php


abstract class aTransactionCS extends aTransactionC
{
    use tTransactionConstructor;

    /* 'property' => '[fieldType, isObject]' or 'formatType' */
    protected static $fieldSet = array(      /* overrided */
        'data' => [TransactionFieldClassEnum::SHORT_DATA, 'getRaw'],
    );

    public function createRaw() : aFieldSet
    {
        $this->rawTransactionCS();

        $this->compositeRaw();

        return $this;
    }

    protected function rawTransactionCS() : void
    {
        if ($this->data === null) {
            throw new Exception($this->getName() . " Bad code - data cannot be null");
        }

        if (!($this->data instanceof aTransactionData)) {
            throw new Exception($this->getName() . " Bad code - data must be insatance of aTransactionData");
        }

        $this->raw = ShortDataTransactionField::pack($this, $this->data->getRaw());
    }
}