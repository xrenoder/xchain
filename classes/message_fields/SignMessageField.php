<?php


class SignMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::SIGN;  /* overrided */

    public function check(): bool
    {
        /* @var aSignMessage $message */
        $message = $this->getMessage();

        $remoteAddress = $message->getRemoteAddress();

        if ($remoteAddress === null) {
            throw new Exception("Bad code - public key must be here!!! Repair method AddrMessageField::check() and AuthorPublicKeyMessageField::check()");
        }

        if (!$remoteAddress->verifyBin($message->getSignature(), $message->getSignedData())) {
            $this->dbg("BAD DATA message signature is bad");
            $this->getLegate()->setBadData();
            return false;
        }

        return true;
    }
}