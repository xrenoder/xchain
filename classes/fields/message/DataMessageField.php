<?php


class DataMessageField extends aMessageField
{
    public function checkValue() : bool
    {
        $dataClass = $this->getMessage()->getDataClass();

        if (!$dataClass) {
            throw new Exception($this->getName() . " Bad code - dataClass must be defined");
        }

        if (!MessageDataClassEnum::isSetItem($dataClass)) {
            $this->err($this->getName() . " parsing error: object cannot be created from type " . $this->getValue());
            $this->parsingError = true;
            return false;
        }

        return true;
    }

    public function setObject() : void
    {
        $message = $this->getMessage();
        $this->object = aMessageData::spawn($message, $message->getDataClass());
        $this->object->setRaw($this->getRawWithoutLength());
        $this->object->parseRaw();
    }

    public function checkObject() : bool
    {
        if ($this->object->isParsingError()) {
            $this->parsingError = true;
            return false;
        }

        return true;
    }

    public function postPrepare() :  bool
    {
        /** @var aMessage $message */
        $message = $this->getMessage();
        $message->setSignedData($this->getRawWithLength() . $message->getSignedData());

        return true;
    }
}