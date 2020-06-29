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
        $rawData = ShortDataTransactionField::pack($this, $this->data->getRaw());

        $this->rawTransactionCa();

        $this->signedData .= $rawData;
        $this->raw .= $rawData;
    }
}