<?php


class PubKeyDbRowField extends aDbRowField
{
    public function setObject() : void
    {
        $this->object = Address::createFromPublic($this->getLocator(), $this->getValue());
    }
}