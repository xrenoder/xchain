<?php


class NonceTransactionField extends aTransactionField
{
    public function postPrepare() :  bool
    {
        $this->getTransaction()->addSignedData($this->getRawWithLength());

        $this->getTransaction()->setHash();

        return true;
    }
}