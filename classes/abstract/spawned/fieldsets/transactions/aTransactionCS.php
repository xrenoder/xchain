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
        $rawData = ShortDataTransactionField::pack($this, $this->data->getRaw());

        $this->signedData = $rawData;
        $this->raw = $rawData;
    }
}