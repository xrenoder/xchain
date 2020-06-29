<?php


class BlockDbField extends aDbField
{
    public function setObject() : void
    {
        $this->object = Block::createFromRaw($this->getLocator(), $this->getValue());
    }
}