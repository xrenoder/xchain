<?php


trait tFieldsCreateAddressFromAddr
{
    public function checkValue() : bool
    {
        if (!Address::checkAddressBin($this->getValue())) {
            $this->err($this->getName() . " BAD DATA address is bad " . Address::binToBase16($this->getValue()));
            $this->parsingError = true;
            return false;
        }

        return true;
    }

    public function setObject() : void
    {
        $this->object = Address::createFromAddress($this->getLocator(), $this->getValue());
    }
}