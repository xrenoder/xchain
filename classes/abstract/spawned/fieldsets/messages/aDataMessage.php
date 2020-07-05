<?php


abstract class aDataMessage extends aSimpleAddressMessage
{
    use tMessageConstructor;

    /* 'property' => [fieldType, isObject] */
    protected static $fieldSet = array(
        'authorAddress' => [MessageFieldClassEnum::PUBKEY, 'getPublicKeyBin'],
        'data' => [MessageFieldClassEnum::DATA, 'getRaw'],
    );

    /** @var Address  */
    protected $authorAddress = null;
    public function setAuthorAddress(Address $val) : self {$this->authorAddress = $val; return $this;}
    public function getAuthorAddress() : Address {return $this->authorAddress;}

    /** @var string  */
    protected $rawPublicKey = null;
    public function setRawPublicKey(string &$val) : self {$this->rawPublicKey = $val; return $this;}
    public function &getRawPublicKey() : string {return $this->rawPublicKey;}

    public function setMaxLen() : aMessage
    {
        $this->maxLen = 0;

        return $this;
    }

    public function createRaw() : aFieldSet
    {
        $this->rawDataMessage();

        $this->compositeRaw();

        return $this;
    }

    protected function rawDataMessage() : void
    {
        $rawPublicKey = AuthorPublicKeyMessageField::pack($this, $this->getAuthorAddress()->getPublicKeyBin());
        $rawData = DataMessageField::pack($this,$this->data->getRaw());

        $this->rawSimpleAddressMessage();

        $this->signedData = $rawPublicKey . $rawData . $this->signedData;

        $this->raw .= $rawPublicKey . $rawData;
    }
}