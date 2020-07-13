<?php


abstract class aTransactionAaS extends aTransactionAa
{
    use tTransactionConstructor;

    /* 'property' => '[fieldType, isObject]' or 'formatType' */
    protected static $fieldSet = array(      /* overrided */
        'data' => [TransactionFieldClassEnum::SHORT_DATA, 'getRaw'],
    );

    public function createRaw() : aFieldSet
    {
        $this->rawTransactionAaS();

        $this->compositeRaw();

        return $this;
    }

    protected function rawTransactionAaS() : void
    {
        if ($this->data === null) {
            throw new Exception($this->getName() . " Bad code - data cannot be null");
        }

        if (!($this->data instanceof aTransactionData)) {
            throw new Exception($this->getName() . " Bad code - data must be insatance of aTransactionData");
        }

        $this->rawTransactionAa();
        $this->raw .= ShortDataTransactionField::pack($this, $this->data->getRaw());
    }
}