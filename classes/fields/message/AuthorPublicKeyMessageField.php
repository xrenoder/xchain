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

        /** @var aAuthorPublicKeyMessage $message */
        $message = $this->getMessage();

        if (Address::getAddrFromPublicKey($this->getValue()) === $message->getRemoteAddress()->getAddressBin()) {
            $this->notNeedCreateNewObject = true;
        }

        return true;
    }

    public function setObject() : void
    {
        if ($this->notNeedCreateNewObject) {
            /** @var aAuthorPublicKeyMessage $message */
            $message = $this->getMessage();
            $remoteAddress = $message->getRemoteAddress();
            $remoteAddress->addPublicKey($this->getValue());
            $this->object = $remoteAddress;
        } else {
            $this->object = Address::createFromPublic($this->getLocator(), $this->getValue());
        }
    }

    public function postPrepare() :  bool
    {
        /* @var aMessage $message */
        $message = $this->getMessage();
        $message->setSignedData($this->getRawWithLength() . $message->getSignedData());

        return true;
    }
}