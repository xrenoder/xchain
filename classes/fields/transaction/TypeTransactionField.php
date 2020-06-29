<?php


class TypeTransactionField extends aTransactionField
{
    public function postPrepare() :  bool
    {
        /* @var aTransaction $transaction */
        $transaction = $this->getTransaction();
        $transaction->setSignedData($this->getRawWithLength());

        return true;
    }
}