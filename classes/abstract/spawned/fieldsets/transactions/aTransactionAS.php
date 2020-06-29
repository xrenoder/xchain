<?php


abstract class aTransactionAS extends aTransactionA
{
    use tTransactionConstructor;

    /* 'property' => '[fieldType, isObject]' or 'formatType' */
    protected static $fieldSet = array(      /* overrided */
        'data' => [TransactionFieldClassEnum::SHORT_DATA, 'getRaw'],
    );

    public function createRaw() : aFieldSet
    {
        $this->rawTransactionAS();

        $this->compositeRaw();

        return $this;
    }

    protected function rawTransactionAS() : void
    {
        $rawData = ShortDataTransactionField::pack($this, $this->data->getRaw());

        $this->rawTransactionA();

        $this->signedData .= $rawData;
        $this->raw .= $rawData;
    }
}