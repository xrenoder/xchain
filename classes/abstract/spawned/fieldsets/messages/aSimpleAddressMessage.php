<?php


abstract class aSimpleAddressMessage extends aSimpleMessage
{
    use tMessageConstructor;

    /* 'property' => [fieldType, isObject] */
    protected static $fieldSet = array(
        'senderAddress' => [MessageFieldClassEnum::SENDER, 'getAddressBin'],
    );

    /** @var Address  */
    protected $senderAddress = null;
    public function setSenderAddress(Address $val) : self {$this->senderAddress = $val; return $this;}
    public function getSenderAddress() : Address {return $this->senderAddress;}

    public function setMaxLen() : aMessage
    {
        $this->maxLen = MessageFieldClassEnum::getSimpleAddrMaxLen();

        return $this;
    }

    public function createRaw() : aFieldSet
    {
        $this->rawSimpleAddressMessage();

        $this->compositeRaw();

        return $this;
    }

    protected function rawSimpleAddressMessage() : void
    {
        $rawAddress = SenderMessageField::pack($this, $this->getLocator()->getMyAddress()->getAddressBin());

        $this->rawSimpleMessage();

        $this->raw .=  $rawAddress;
        $this->signedData .= $rawAddress;
    }
}