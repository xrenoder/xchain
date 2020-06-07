<?php


class PublicKeyTransactionData extends aTransactionData
{
    /**
     * 'propertyName' => fieldFormat
     * @var array
     */
    protected static $fieldSet = array(
        TransactionDataFieldClassEnum::PUBKEY => 'publicKey',
    );

    /** @var string  */
    protected $publicKey = null;
    public function setPublicKey(string $val) : self {$this->publicKey = $val; return $this;}
    public function getPublicKey() : ?string {return $this->publicKey;}
}