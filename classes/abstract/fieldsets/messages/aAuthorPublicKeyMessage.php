<?php


abstract class aAuthorPublicKeyMessage extends aDataMessage
{
    /** @var int  */
    protected $maxLen = MessageFieldClassEnum::UNKNOWN_LEN;   /* overrided */

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(
        MessageFieldClassEnum::PUBKEY =>    'authorPublicKey',
    );

    /** @var string  */
    protected $authorPublicKey = null;
    public function getAuthorPublicKey() : string {return $this->authorPublicKey;}

    protected function __construct(aBase $parent)
    {
        parent::__construct($parent);
        $this->fields = array_replace($this->fields, self::$fieldSet);
    }

    /**
     * @return string
     */
    public function createMessageString() : string
    {
        $body = $this->bodyAuthorPublicKey();

        return $this->compositeMessage($body);
    }

    protected function bodyAuthorPublicKey() : string
    {
        if (empty($this->outData) || !isset($this->outData[static::AUTHKEY])) {
            throw new Exception("Bad coding: author public key must be here!!!");
        }

        $pubKey = $this->outData[static::AUTHKEY];

        if (strlen($pubKey) !== Address::PUBLIC_BIN_LEN) {
            throw new Exception("Bad coding: author public key bad length!!!");
        }

        $bodyParent = $this->bodyData();

        $pubkeyField = AuthorPublicKeyMessageField::pack($this,$pubKey);

        $this->signedData = $pubkeyField . $this->signedData;

        return $bodyParent . $pubkeyField;
    }
}