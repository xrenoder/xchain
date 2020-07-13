<?php


class SignTransactionField extends aTransactionField
{
    public function checkValue(): bool
    {
        /** @var aTransaction $transaction */
        $transaction = $this->getTransaction();
        $authorAddress = $transaction->getAuthorAddress();

        if ($authorAddress === null) {
            $this->err($this->getName() . " BAD DATA transaction author address must be not null");
            $this->parsingError = true;
            return false;
        }

        if (!$authorAddress->verifyBin($this->getValue(), $transaction->getSignedData())) {
            $this->err($this->getName() . " BAD DATA transaction signature is bad " . $this->getValue());
            $this->parsingError = true;
            return false;
        }

        return true;
    }

    public function postPrepare() :  bool
    {
        return true;
    }
}