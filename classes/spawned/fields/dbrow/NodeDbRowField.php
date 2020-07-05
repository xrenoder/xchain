<?php


class NodeDbRowField extends aDbRowField
{
    public function setObject() :  void
    {
        $this->object = aNode::spawn($this->getRow(), $this->getValue());
    }
}