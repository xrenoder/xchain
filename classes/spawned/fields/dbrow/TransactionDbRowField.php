<?php


class TransactionDbRowField extends aDbRowField
{
    /** @var int  */
    private $transactionType = null;

    public function checkValue() : bool
    {
        $this->transactionType = aTransaction::parseType($this, $this->getRawWithoutLength());

        if ($this->transactionType === null) {
            $this->err($this->getName() . " parsing error: transaction-object cannot be created from type " . $this->getValue());
            $this->parsingError = true;
            return false;
        }

        return true;
    }

    public function setObject() : void
    {
        $this->object = aTransaction::spawn($this->getParent(), $this->transactionType);
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