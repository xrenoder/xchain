<?php


abstract class aTransactionCaS extends aTransactionCa
{
    use tTransactionConstructor;

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(      /* overrided */
        TransactionFieldClassEnum::SHORT_DATA =>    self::DATA_PROPERTY,
    );

    /**
     * @return string
     */
    public function createRaw()
    {
        $this->rawTransactionCaS();

        $this->compositeRaw();

        return $this;
    }

    protected function rawTransactionCaS() : void
    {
        $this->createRawData();

        $this->rawTransactionCa();

        $rawData = ShortDataTransactionField::pack($this, $this->rawData);

        $this->signedData .= $rawData;
        $this->raw .= $rawData;
    }
}