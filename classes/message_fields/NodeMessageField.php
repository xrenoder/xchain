<?php


class NodeMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::NODE;  /* overrided */

    public function check(): bool
    {
        $socket = $this->getSocket();

        $socket->setRemoteNode(aNode::spawn($this->getApp(), $this->getMessage()->getRemoteNodeId()));
        $socket->checkNodesCompatiblity();

        return true;
    }
}