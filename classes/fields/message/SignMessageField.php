<?php


class SignMessageField extends aMessageField
{
    /** @var int  */
    protected $id = MessageFieldClassEnum::SIGN;  /* overrided */

    public function check(): bool
    {
        /* @var aSignMessage $message */
        $message = $this->getMessage();

        $remoteAddress = $message->getRemoteAddress();

        if ($remoteAddress === null) {
            throw new Exception("Bad code - public key must be here!!! Repair method AddrMessageField::check() and AuthorPublicKeyMessageField::check()");
        }

        $signedData = $message->getSignedData();

        $this->dbg($this->getName() . " verified data: " . bin2hex($signedData));

        if (!$remoteAddress->verifyBin($message->getSignature(), $signedData)) {
            $this->dbg("BAD DATA message signature is bad");
            $this->getLegate()->setBadData();
            return false;
        }

        return true;
    }
}