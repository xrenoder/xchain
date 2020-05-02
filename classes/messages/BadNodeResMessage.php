<?php
/**
 * Response "Daemon is alive, but your node cannot connect to me, socket will be closed"
 */
class BadNodeResMessage extends aSimpleMessage
{
    /** @var int  */
    protected static $id = MessageClassEnum::BAD_NODE_RES;  /* overrided */
    protected static $needAliveCheck = false;               /* overrided */

    protected function incomingMessageHandler(): bool
    {
//        $this->getSocket()->setFree();

        $this->getSocket()->close();

        return true;
    }
}