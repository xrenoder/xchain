<?php


abstract class aAuthorPublicKeyMessage extends aDataMessage
{
    use tMessageConstructor;

    /* 'property' => '[fieldType, isObject]' or 'formatType' */
    protected static $fieldSet = array(
        'authorAddress' => [MessageFieldClassEnum::PUBKEY, 'getPublicKeyBin'],
    );

    /** @var Address  */
    protected $authorAddress = null;
    public function setAuthorAddress(Address $val) : self {$this->authorAddress = $val; return $this;}
    public function getAuthorAddress() : Address {return $this->authorAddress;}

    public function createRaw() : aFieldSet
    {
        $this->rawAuthorPublicKeyMessage();

        $this->compositeRaw();

        return $this;
    }

    protected function rawAuthorPublicKeyMessage() : void
    {
        $rawPublicKey = AuthorPublicKeyMessageField::pack($this, $this->getAuthorAddress()->getPublicKeyBin());

        $this->rawDataMessage();

        $this->signedData = $rawPublicKey . $this->signedData;
        $this->raw .= $rawPublicKey;
    }
}