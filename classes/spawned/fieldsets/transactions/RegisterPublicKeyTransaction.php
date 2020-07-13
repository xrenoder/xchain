<?php


class RegisterPublicKeyTransaction extends aTransactionCT
{
    protected static $isAuthor = false; /* overrided */

    /** @var int  */
    protected $dataClass = TransactionDataClassEnum::PUBLIC_KEY; /* overrided */

    public function setAuthorPubKeyAddress(Address $val) : self {$this->getData()->setAuthorPubKeyAddress($val); return $this;}
    public function getAuthorPubKeyAddress() : Address {return $this->getData()->getAuthorPubKeyAddress();}

    public function saveAsNewTransaction() : aTransaction
    {
        return $this;
    }

// TODO сделать проверку, чтобы записей в секции блока PublicKeys было не больше записей в блоках авторских транзакций
}