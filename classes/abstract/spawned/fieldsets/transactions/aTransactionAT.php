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
        $rawData = ShortDataTransactionField::pack($this, $this->data->getRaw());

        $this->rawTransactionA();

        $this->signedData .= $rawData;
        $this->raw .= $rawData;
    }
}