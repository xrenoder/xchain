<?php


class PubKeyDbField extends aDbField
{
    public function setObject() : void
    {
        $this->object = Address::createFromPublic($this->getLocator(), $this->getValue());
    }
}