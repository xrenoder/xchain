<?php


class NonceTransactionField extends aTransactionField
{
    public function checkValue() : bool
    {
        if ($this->getValue() < 0) {
            $this->dbg($this->getName() . " BAD DATA nonce must be >=0");
            $this->parsingError = true;
            return false;
        }

        return true;
    }
}