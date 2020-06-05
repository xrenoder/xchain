<?php


class AddrMessageField extends aMessageField
{
    /** @var int  */
    protected $id = MessageFieldClassEnum::ADDR;  /* overrided */

    public function check(): bool
    {
        /* @var aSimpleAddressMessage $message */
        $message = $this->getMessage();
        $legate = $this->getLegate();
        $remoteAddrBin = $message->getRemoteAddrBin();
        $locator = $this->getLocator();

        $message->setSignedData($message->getSignedData() . $this->getRawWithLength());

        if (!Address::checkAddressBin($remoteAddrBin)) {
            $this->dbg("BAD DATA remote address is bad " . Address::binToBase16($remoteAddrBin));
            $legate->setBadData();
            return false;
        }

        $myNodeId = $legate->getMyNodeId();
        $remoteNodeId = $message->getRemoteNodeId();

        if ($remoteNodeId !== NodeClassEnum::CLIENT && $myNodeId !== NodeClassEnum::CLIENT) {
            $savedNodeId = NodeByAddr::create($locator, $remoteAddrBin)->getNodeId();

            if ($savedNodeId === null) {
                $this->dbg("BAD DATA don't know node with address " . Address::binToBase16($remoteAddrBin));
                $legate->setBadData();
                return false;
            }

            if ($savedNodeId !== $remoteNodeId) {
                $this->dbg("BAD DATA address " . Address::binToBase16($remoteAddrBin) . " cannot be node " . NodeClassEnum::getName($remoteNodeId) . " (is node " . NodeClassEnum::getName($savedNodeId) . ")");
                $legate->setBadData();
                return false;
            }

            $savedPubKey = PubKeyByAddr::create($locator, $remoteAddrBin)->getPublicKey();

            if ($savedPubKey === null) {
                $this->dbg("BAD DATA don't know public key for address " . Address::binToBase16($remoteAddrBin));
                $legate->setBadData();
                return false;
            }

// TODO при сохранении публичного ключа в базу проверять соответствие ключа и адреса, чтобы здесь уже не проверять

            $message->setRemoteAddress(Address::createFromPublic($locator, $savedPubKey));
        }

        $this->dbg("Message received from address " . Address::binToBase16($remoteAddrBin));

        return true;
    }
}