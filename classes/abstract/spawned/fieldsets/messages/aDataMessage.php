<?php


abstract class aDataMessage extends aSimpleAddressMessage
{
    use tMessageConstructor;

    /* 'property' => [fieldType, isObject] */
    protected static $fieldSet = array(
        'data' => [MessageFieldClassEnum::DATA, 'getRaw'],
    );

    public function setMaxLen() : aMessage
    {
        $this->maxLen = 0;

        return $this;
    }

    public function createRaw() : aFieldSet
    {
        $this->rawDataMessage();

        $this->compositeRaw();

        return $this;
    }

    protected function rawDataMessage() : void
    {
        $rawData = DataMessageField::pack($this,$this->data->getRaw());

        $this->rawSimpleAddressMessage();

        $this->signedData = $rawData . $this->signedData;

        $this->raw .= $rawData;
    }
}