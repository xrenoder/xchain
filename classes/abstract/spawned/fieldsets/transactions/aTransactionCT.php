<?php


abstract class aTransactionCT extends aTransactionC
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
        $this->rawTransactionCT();

        return $this->compositeRaw();
    }

    protected function rawTransactionCT() : void
    {
        $this->createRawData();

        $rawData = TinyDataTransactionField::pack($this, $this->rawData);

        $this->signedData .= $rawData;
        $this->raw .= $rawData;
    }
}