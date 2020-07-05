<?php


class AuthorPublicKeyMessageField extends aMessageField
{
    /** @var bool  */
    private $notNeedCreateNewObject = false;

    public function checkValue() : bool
    {
        if (!Address::checkAddressBin($this->getValue())) {
            $this->err($this->getName() . " BAD DATA address is bad " . Address::binToBase16($this->getValue()));
            $this->parsingError = true;
            return false;
        }

        /** @var aDataMessage $message */
        $message = $this->getMessage();

        if (Address::getAddrFromPublicKey($this->getValue()) === $message->getSenderAddress()->getAddressBin()) {
            $this->notNeedCreateNewObject = true;
        }

        return true;
    }

    public function setObject() : void
    {
        if ($this->notNeedCreateNewObject) {
            /** @var aDataMessage $message */
            $message = $this->getMessage();
            $senderAddress = $message->getSenderAddress();
            $senderAddress->addPublicKey($this->getValue());
            $this->object = $senderAddress;
        } else {
            $this->object = Address::createFromPublic($this->getLocator(), $this->getValue());
        }
    }

    public function postPrepare() :  bool
    {
        /** @var aDataMessage $message */
        $message = $this->getMessage();
        $message->setRawPublicKey($this->getRawWithLength());

        return true;
    }
}