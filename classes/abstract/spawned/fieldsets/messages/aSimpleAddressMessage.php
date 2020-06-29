<?php


abstract class aSimpleAddressMessage extends aSimpleMessage
{
    use tMessageConstructor;

    /* 'property' => [fieldType, isObject] */
    protected static $fieldSet = array(
        'remoteAddr' => [MessageFieldClassEnum::ADDR, 'getAddressBin'],
    );

    /** @var Address  */
    protected $remoteAddress = null;
    public function setRemoteAddress(Address $val) : self {$this->remoteAddress = $val; return $this;}
    public function getRemoteAddress() : Address {return $this->remoteAddress;}

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
        $rawAddress = AddrMessageField::pack($this, $this->getLocator()->getMyAddress()->getAddressBin());

        $this->rawSimpleMessage();

        $this->raw .=  $rawAddress;
        $this->signedData .= $rawAddress;
    }
}