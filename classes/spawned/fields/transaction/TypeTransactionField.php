<?php


class TypeTransactionField extends aTransactionField
{
    public function postPrepare() :  bool
    {
        $this->getTransaction()->setSignedData($this->getRawWithLength());

        return true;
    }
}