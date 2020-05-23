<?php


class AddrMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::ADDR;  /* overrided */

    public function check(): bool
    {
        /* @var aSimpleAddressMessage $message */
        $message = $this->getMessage();
        $legate = $this->getLegate();
        $remoteAddrBin = $message->getRemoteAddrBin();
        $locator = $this->getLocator();

        if (!Address::checkAddressBin($remoteAddrBin)) {
            $this->dbg("BAD DATA remote address is bad " . Address::binToBase16($remoteAddrBin));
            $legate->setBadData();
            return false;
        }

        $myNodeId = $legate->getMyNodeId();
        $remoteNodeId = $message->getRemoteNodeId();

        if ($remoteNodeId !== NodeClassEnum::CLIENT_ID && $myNodeId !== NodeClassEnum::CLIENT_ID) {
            $pubKeyAndNode = PubKeyNodeByAddr::create($locator, $remoteAddrBin);
            $savedNodeId = $pubKeyAndNode->getNodeId();

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

            $message->setRemoteAddress(Address::createFromPublic($locator, $pubKeyAndNode->getPublicKey()));
// TODO при сохранении публичного ключа в базу проверять соответствие ключа и адреса
        }

        $this->dbg("Message received from address " . Address::binToBase16($remoteAddrBin));

        $message->setSignedData($message->getSignedData() . $this->raw);

        return true;
    }
}