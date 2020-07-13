<?php


class SenderMessageField extends aMessageField
{
    /** @var Address  */
    private $savedAddressWithPubKey = null;

    public function checkValue() : bool
    {
        if (!Address::checkAddressBin($this->getValue())) {
            $this->err($this->getName() . " BAD DATA address is bad " . Address::binToBase16($this->getValue()));
            $this->parsingError = true;
            return false;
        }

        /** @var aSimpleAddressMessage $message */
        $message = $this->getMessage();

        $senderNodeType = $message->getSenderNodeType();

// if my node or remote node is client, not check node & public key in DB
        if ($senderNodeType !== NodeEnum::CLIENT && $message->getMyNodeType() !== NodeEnum::CLIENT) {
            $locator = $this->getLocator();
            $savedNodeType = NodeByAddrDbRow::create($locator, $this->getValue())->getNodeType();

            if ($savedNodeType === null) {
                $this->err($this->getName() . " BAD DATA don't know node with address " . Address::binToBase16($this->getValue()));
                $this->parsingError = true;
                return false;
            }

            if ($savedNodeType !== $senderNodeType) {
                $this->err($this->getName() . " BAD DATA address " . Address::binToBase16($this->getValue()) . " cannot be node " . NodeEnum::getName($senderNodeType) . " (is node " . NodeEnum::getName($savedNodeType) . ")");
                $this->parsingError = true;
                return false;
            }

            $this->savedAddressWithPubKey = PubKeyByAddrDbRow::create($locator, $this->getValue())->getAddressWithPubKey();

            if ($this->savedAddressWithPubKey === null) {
                $this->err($this->getName() . " BAD DATA don't know public key for sender address " . Address::binToBase16($this->getValue()));
                $this->parsingError = true;
                return false;
            }
        }

        return true;
    }

    public function setObject() : void
    {
        if ($this->savedAddressWithPubKey !== null) {
            $this->object = $this->savedAddressWithPubKey;
        } else {
            $this->object = Address::createFromAddress($this->getLocator(), $this->getValue());
        }
    }
}