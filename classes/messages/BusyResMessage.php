<?php
/**
 * Response "Daemon is alive, but busy, cannot accept connections, socket will be closed"
 */
class BusyResMessage extends aSimpleMessage
{
    /** @var int  */
    protected static $id = MessageClassEnum::BUSY_RES;  /* overrided */
    protected static $needAliveCheck = false;           /* overrided */

    /**
     * @return bool
     */
    protected function incomingMessageHandler() : bool
    {
//        $this->getSocket()->setFree();

        $this->getSocket()->close();

        return true;
    }
}