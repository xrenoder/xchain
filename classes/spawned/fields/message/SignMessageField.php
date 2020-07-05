<?php


class SignMessageField extends aMessageField
{
    public function checkValue(): bool
    {
        /* @var aSignedMessage $message */
        $message = $this->getMessage();

        $senderAddress = $message->getSenderAddress();

        if ($senderAddress->isAddressOnly()) {
            $this->err($this->getName() . " BAD DATA message sender public key must be here!!!");
            $this->parsingError = true;
            return false;
        }

        $signedData = $message->getSignedData();

        $this->dbg($this->getName() . " verified data: " . bin2hex($signedData));

        if (!$senderAddress->verifyBin($this->getValue(), $signedData)) {
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