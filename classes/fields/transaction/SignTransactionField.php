<?php


class SignTransactionField extends aTransactionField
{
    public function checkValue(): bool
    {
        /** @var aTransaction $transaction */
        $transaction = $this->getTransaction();
        $authorAddress = $transaction->getAuthorAddress();

        if ($authorAddress->isAddressOnly()) {
            $this->err($this->getName() . " BAD DATA signature cannot be verified - need author public key for" . $authorAddress->getAddressHuman());
            $this->parsingError = true;
            return false;
        }

        if (!$authorAddress->verifyBin($this->getValue(), $transaction->getSignedData())) {
            $this->err($this->getName() . " BAD DATA signature is bad " . $this->getValue());
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