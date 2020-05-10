<?php


class NodeMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::MESS_FLD_NODE;  /* overrided */

    public function check(): bool
    {
        $socket = $this->getSocket();

        $socket->setRemoteNode(aNode::spawn($this->getApp(), $this->getMessage()->getRemoteNodeId()));
        $socket->checkNodesCompatiblity();
// TODO добавить проверку в блокчейне, может ли отправитель сообщения исполнять роль той ноды, которой представляется
        return true;
    }
}