<?php


class AddrMessageField extends aMessageField
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

        $remoteNodeType = $message->getRemoteNode()->getType();

// if my node or remote node is client, not check node & public key in DB
        if ($remoteNodeType !== NodeClassEnum::CLIENT && $message->getMyNode()->getType() !== NodeClassEnum::CLIENT) {
            $locator = $this->getLocator();
            $savedNode = NodeByAddr::create($locator, $this->getValue())->getNode();

            if ($savedNode === null) {
                $this->err($this->getName() . " BAD DATA don't know node with address " . Address::binToBase16($this->getValue()));
                $this->parsingError = true;
                return false;
            }

            if ($savedNode->getType() !== $remoteNodeType) {
                $this->err($this->getName() . " BAD DATA address " . Address::binToBase16($this->getValue()) . " cannot be node " . NodeClassEnum::getName($remoteNodeType) . " (is node " . $savedNode->getName() . ")");
                $this->parsingError = true;
                return false;
            }

            $this->savedAddressWithPubKey = PubKeyByAddr::create($locator, $this->getValue())->getAddressWithPubKey();

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