<?php


class PublicKeyTransactionData extends aTransactionData
{
    /**
     * 'propertyName' => fieldFormat
     * @var array
     */
    protected static $fieldSet = array(
        'publicKey' =>        TransactionDataFieldClassEnum::PUBKEY,
    );

    /** @var string  */
    protected $publicKey = null;
    public function setPublicKey(string $val) : self {$this->publicKey = $val; return $this;}
    public function getPublicKey() : ?string {return $this->publicKey;}
}