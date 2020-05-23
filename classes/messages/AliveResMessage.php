<?php
/**
 * Response "Daemon is alive"
 */
class AliveResMessage extends aSimpleAddressMessage
{
    /** @var int  */
    protected static $id = MessageClassEnum::ALIVE_RES; /* overrided */
    protected static $needAliveCheck = false;           /* overrided */

    /**
     * @return bool
     */
    protected function incomingMessageHandler() : bool
    {
        $this->getLegate()->setNeedCloseSocket();
        return self::MESSAGE_PARSED;
    }
}