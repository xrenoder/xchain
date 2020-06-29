<?php


class RegisterPublicKeyTransaction extends aTransactionAS
{
    /** @var int  */
    protected $dataClass = TransactionDataClassEnum::PUBLIC_KEY; /* overrided */

    public function setAuthorPubKeyAddress(Address $val) : self {$this->getData()->setAuthorPubKeyAddress($val); return $this;}
    public function getAuthorPubKeyAddress() : Address {return $this->getData()->getAuthorPubKeyAddress();}
}