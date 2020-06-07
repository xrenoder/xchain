<?php


abstract class aTransactionCaT extends aTransactionCa
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
    public function createRaw() : ?string
    {
        $this->rawTransactionCaT();

        return $this->compositeRaw();
    }

    protected function rawTransactionCaT() : void
    {
        $this->createRawData();

        $this->rawTransactionCa();

        $rawData = TinyDataTransactionField::pack($this, $this->rawData);

        $this->signedData .= $rawData;
        $this->raw .= $rawData;
    }
}