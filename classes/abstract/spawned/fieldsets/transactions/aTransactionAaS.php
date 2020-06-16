<?php


abstract class aTransactionAaS extends aTransactionAa
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
    public function createRaw() : ?string
    {
        $this->rawTransactionAaS();

        return $this->compositeRaw();
    }

    protected function rawTransactionAaS() : void
    {
        $this->createRawData();

        $this->rawTransactionAa();

        $rawData = ShortDataTransactionField::pack($this, $this->rawData);

        $this->signedData .= $rawData;
        $this->raw .= $rawData;
    }
}