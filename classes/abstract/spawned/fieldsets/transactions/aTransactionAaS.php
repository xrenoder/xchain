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
        $rawData = ShortDataTransactionField::pack($this, $this->data->getRaw());

        $this->rawTransactionAa();

        $this->signedData .= $rawData;
        $this->raw .= $rawData;
    }
}