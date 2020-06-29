<?php


class PublicKeyTransactionData extends aTransactionData
{
    /* 'property' => '[fieldType, objectMethod]' or 'formatType' */
    protected static $fieldSet = array(
        'authorPubKeyAddress' => [TransactionDataFieldClassEnum::PUBKEY, 'getPublicKeyBin'],
    );

    /** @var Address  */
    protected $authorPubKeyAddress = null;
    public function setAuthorPubKeyAddress(Address $val) : self {$this->authorPubKeyAddress = $val; return $this;}
    public function getAuthorPubKeyAddress() : Address {return $this->authorPubKeyAddress;}
}