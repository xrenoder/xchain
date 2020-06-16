<?php


abstract class aTransactionCS extends aTransactionC
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
        $this->rawTransactionCS();

        return $this->compositeRaw();
    }

    protected function rawTransactionCS() : void
    {
        $this->createRawData();

        $rawData = ShortDataTransactionField::pack($this, $this->rawData);

        $this->signedData .= $rawData;
        $this->raw .= $rawData;
    }
}