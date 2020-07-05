<?php


class SignBlockField extends aBlockField
{
    public function checkValue(): bool
    {
        $block = $this->getBlock();

        if (!$block->getSignerAddress()->verifyBin($this->getValue(), $block->getSignedData())) {
            $this->err($this->getName() . " BAD DATA block signature is bad " . $this->getValue());
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