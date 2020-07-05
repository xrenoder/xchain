<?php


class BlockDbRowField extends aDbRowField
{
    public function setObject() : void
    {
        $this->object = Block::createFromRaw($this->getLocator(), $this->getValue());
    }
}