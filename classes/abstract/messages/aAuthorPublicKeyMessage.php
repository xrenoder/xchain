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

        return $this->compileMessage($body);
    }

    protected function bodyAuthorPublicKey() : string
    {
        if (empty($this->outgoingString) || !isset($this->outgoingString[static::AUTHKEY])) {
            throw new Exception("Bad coding: author public key must be here!!!");
        }

        $pubKey = $this->outgoingString[static::AUTHKEY];

        if (strlen($pubKey) !== Address::PUBLIC_BIN_LEN) {
            throw new Exception("Bad coding: author public key bad length!!!");
        }

        $bodyParent = $this->bodyData();

        $pubkeyField = AuthorPublicKeyMessageField::packField($pubKey);

        $this->signedData = $pubkeyField . $this->signedData;

        return $bodyParent . $pubkeyField;
    }
}