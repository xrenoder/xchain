<?php


class TransactionMessageDataField extends aMessageDataField
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
        $message = $this->getMessage();

        $this->object = aTransaction::spawn($message, $this->transactionType);
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