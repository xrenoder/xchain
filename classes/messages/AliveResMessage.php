<?php
/**
 * Response "Daemon is alive"
 */
class AliveResMessage extends aSimpleMessage
{
    /** @var int  */
    protected static $id = MessageClassEnum::ALIVE_RES; /* overrided */
    protected static $needAliveCheck = false;           /* overrided */

    /**
     * @return bool
     */
    protected function incomingMessageHandler() : bool
    {
//        $this->getSocket()->setFree();

        $this->getSocket()->setAliveChecked();
        $this->getSocket()->cleanMessage();
        $this->getSocket()->addDelayedOutData();

        return true;
    }
}