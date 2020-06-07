<?php


abstract class aTransactionAaT extends aTransactionAa
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
        $this->rawTransactionAaT();

        return $this->compositeRaw();
    }

    protected function rawTransactionAaT() : void
    {
        $this->createRawData();

        $this->rawTransactionAa();

        $rawData = TinyDataTransactionField::pack($this, $this->rawData);

        $this->signedData .= $rawData;
        $this->raw .= $rawData;
    }
}