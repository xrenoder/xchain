<?php


abstract class aTransactionCaS extends aTransactionCa
{
    use tTransactionConstructor;

    /* 'property' => '[fieldType, isObject]' or 'formatType' */
    protected static $fieldSet = array(      /* overrided */
        'data' => [TransactionFieldClassEnum::SHORT_DATA, 'getRaw'],
    );

    public function createRaw() : aFieldSet
    {
        $this->rawTransactionCaS();

        $this->compositeRaw();

        return $this;
    }

    protected function rawTransactionCaS() : void
    {
        if ($this->data === null) {
            throw new Exception($this->getName() . " Bad code - data cannot be null");
        }

        if (!($this->data instanceof aTransactionData)) {
            throw new Exception($this->getName() . " Bad code - data must be insatance of aTransactionData");
        }

        $this->rawTransactionCa();
        $this->raw .= ShortDataTransactionField::pack($this, $this->data->getRaw());
    }
}