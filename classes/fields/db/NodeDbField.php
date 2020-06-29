<?php


class NodeDbField extends aDbField
{
    public function setObject() :  void
    {
        $this->object = aNode::spawn($this->getRow(), $this->getValue());
    }
}