<?php


abstract class aTransactionAS extends aTransactionA
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
        $this->rawTransactionAS();

        $this->compositeRaw();

        return $this;
    }

    protected function rawTransactionAS() : void
    {
        $this->createRawData();

        $this->rawTransactionA();

        $rawData = ShortDataTransactionField::pack($this, $this->rawData);

        $this->signedData .= $rawData;
        $this->raw .= $rawData;
    }
}