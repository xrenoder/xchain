<?php


class AddrMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::ADDR;  /* overrided */

    public function check(): bool
    {
        /* @var aSimpleAddressMessage $message */
        $message = $this->getMessage();
        $socket = $this->getSocket();
        $addrBin = $message->getRemoteAddrBin();
        $app = $this->getApp();

        if (!Address::checkAddressBin($addrBin)) {
            $this->dbg("BAD DATA address is bad " . Address::binToBase16($addrBin));
            return $socket->badData();
        }

        $socketAddress = $socket->getRemoteAddress();

        if ($socketAddress !== null) {
            if ($socketAddress->getAddressBin() !== $addrBin) {
                $this->dbg("BAD DATA received address " . Address::binToBase16($addrBin) . " is different than recieved before " . Address::binToBase16($socketAddress->getAddressHuman()));
                return $socket->badData();
            }
        } else {
            $pubKeyAndNode = PubKeyNodeByAddr::create($app, $addrBin);
            $savedNodeId = $pubKeyAndNode->getNodeId();
            $savedPubKey = $pubKeyAndNode->getPublicKey();

            $remoteNodeId = $message->getRemoteNodeId();
            $myNodeId = $socket->getMyNodeId();

            if ($remoteNodeId !== NodeClassEnum::CLIENT_ID && $myNodeId !== NodeClassEnum::CLIENT_ID) {
                if ($savedNodeId === null) {
                    $this->dbg("BAD DATA don't know node with address " . Address::binToBase16($addrBin));
                    return $socket->badData();
                }

                if ($savedNodeId !== $remoteNodeId) {
                    $this->dbg("BAD DATA address " . Address::binToBase16($addrBin) . " cannot be node " . NodeClassEnum::getName($remoteNodeId) . " (is node " . NodeClassEnum::getName($savedNodeId) . ")");
                    return $socket->badData();
                }
            }

            if ($savedPubKey !== null) {
                $remoteAddress = Address::createFromPublic($app, $savedPubKey);
            } else {
                $remoteAddress = Address::createFromAddress($app, $addrBin);
            }

            $socket->setRemoteAddress($remoteAddress);
        }

        $this->dbg("Message received from address " . Address::binToBase16($addrBin));

        return true;
    }
}