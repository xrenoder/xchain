<?php


trait tTransactionFieldDataSetObject
{
    public function checkValue() : bool
    {
        $dataClass = $this->getTransaction()->getDataClass();

        if (!$dataClass) {
            throw new Exception($this->getName() . " Bad code - dataClass must be defined");
        }

        if (!TransactionDataClassEnum::isSetItem($dataClass)) {
            $this->err($this->getName() . " parsing error: object cannot be created from type " . $this->getValue());
            $this->parsingError = true;
            return false;
        }

        return true;
    }

    public function setObject() : void
    {
        $transaction = $this->getTransaction();
        $this->object = aTransactionData::spawn($transaction, $transaction->getDataClass());
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
}