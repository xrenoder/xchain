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