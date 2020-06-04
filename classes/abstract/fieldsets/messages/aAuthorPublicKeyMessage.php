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
    public function setAuthorPublicKey(string $val) : self {$this->authorPublicKey = $val; return $this;}
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
        if ($this->authorPublicKey === null) {
            throw new Exception("Bad coding: author public key must be here!!!");
        }

        if (strlen($this->authorPublicKey) !== Address::PUBLIC_BIN_LEN) {
            throw new Exception("Bad coding: author public key bad length!!!");
        }

        $bodyParent = $this->bodyData();

        $pubkeyField = AuthorPublicKeyMessageField::pack($this,$this->authorPublicKey);

        $this->signedData = $pubkeyField . $this->signedData;

        return $bodyParent . $pubkeyField;
    }
}