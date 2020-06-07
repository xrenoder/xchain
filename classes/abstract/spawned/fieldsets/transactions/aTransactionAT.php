<?php


abstract class aTransactionAT extends aTransactionA
{
    use tTransactionConstructor;

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(      /* overrided */
        TransactionFieldClassEnum::TINY_DATA =>    self::DATA_PROPERTY,
    );

    /**
     * @return string
     */
    public function createRaw() : string
    {
        $this->rawTransactionAT();

        return $this->compositeRaw();
    }

    protected function rawTransactionAT() : void
    {
        $this->createRawData();

        $this->rawTransactionA();

        $rawData = TinyDataTransactionField::pack($this, $this->rawData);

        $this->signedData .= $rawData;
        $this->raw .= $rawData;
    }
}