<?php


class SignTransactionField extends aTransactionField
{
    public function checkValue(): bool
    {
        /** @var aTransaction $transaction */
        $transaction = $this->getTransaction();

        if (!$transaction->getAuthorAddress()->verifyBin($this->getValue(), $transaction->getSignedData())) {
            $this->err($this->getName() . " BAD DATA transaction signature is bad " . $this->getValue());
            $this->parsingError = true;
            return false;
        }

        return true;
    }

    public function postPrepare() :  bool
    {
        $this->getTransaction()->setHash();

        return true;
    }
}