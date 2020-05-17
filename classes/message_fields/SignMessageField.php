<?php


class SignMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::SIGN;  /* overrided */

    public function check(): bool
    {
        /* @var aDataSignMessage $message */
        $message = $this->getMessage();
        $socket = $this->getSocket();

        $remoteNodeId = $socket->getRemoteNode()->getId();
        $myNodeId = $socket->getMyNodeId();

        if ($remoteNodeId === NodeClassEnum::CLIENT_ID || $myNodeId === NodeClassEnum::CLIENT_ID) {
            $this->dbg("BAD DATA client cannot send and receive signed data message");
            return $socket->badData();
        }

        $remoteAddress = $socket->getRemoteAddress();

        if ($remoteAddress !== null && $remoteAddress->getPublicKeyBin() !== null) {
            $signedData = $remoteNodeId . $remoteAddress->getAddressBin() . $message->getSendingTime() . $message->getData();

            if (!$remoteAddress->verifyBin($message->getSignature(), $signedData)) {
                $this->dbg("BAD DATA signature is bad");
                return $socket->badData();
            }
        } else {
            throw new Exception("Bad code - public key must be here!!! Repair method AddrMessageField::check()");
        }

        return true;
    }
}