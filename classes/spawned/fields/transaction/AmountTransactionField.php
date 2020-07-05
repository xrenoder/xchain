<?php


class AmountTransactionField extends aTransactionField
{
    public function checkValue() : bool
    {
        if ($this->getValue() <= 0) {
            $this->dbg($this->getName() . " BAD DATA amount must be >0");
            $this->parsingError = true;
            return false;
        }

        return true;
    }
}