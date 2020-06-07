<?php


class RegisterPublicKeyTransaction extends aTransactionAT
{
    /** @var int  */
    protected $dataClassId = TransactionDataClassEnum::PUBLIC_KEY; /* overrided */

    public function setPublicKey(string $val) : self {$this->data->setPublicKey($val); return $this;}
    public function getPublicKey() : ?string {return $this->data->getPublicKey();}
}