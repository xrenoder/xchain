<?php


class SignMessageField extends aMessageField
{
    public function checkValue(): bool
    {
        /* @var aSignedMessage $message */
        $message = $this->getMessage();

        $remoteAddress = $message->getRemoteAddress();

        if ($remoteAddress->isAddressOnly()) {
            $this->err($this->getName() . " BAD DATA (or code?) - public key must be here!!!");
            $this->parsingError = true;
            return false;
        }

        $signedData = $message->getSignedData();

        $this->dbg($this->getName() . " verified data: " . bin2hex($signedData));

        if (!$remoteAddress->verifyBin($this->getValue(), $signedData)) {
            $this->err($this->getName() . " BAD DATA message signature is bad");
            $this->parsingError = true;
            return false;
        }

        return true;
    }

    public function postPrepare() :  bool
    {
        return true;
    }
}