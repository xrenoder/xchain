<?php


class NodeMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::MESS_FLD_NODE;  /* overrided */

    public function check(): bool
    {
        $this->getSocket()->setRemoteNode(aNode::spawn($this->getApp(), $this->getMessage()->getRemoteNodeId()));
        $this->getSocket()->checkNodesCompatiblity();
// TODO добавить проверку в блокчейне, может ли отправитель сообщения исполнять роль той ноды, которой представляется
        return true;
    }
}