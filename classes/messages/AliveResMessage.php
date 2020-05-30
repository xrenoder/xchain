<?php
/**
 * Response "Daemon is alive"
 */
class AliveResMessage extends aSignMessage
{
    /**
     * @return bool
     */
    protected function incomingMessageHandler() : bool
    {
        $this->getLegate()->setNeedCloseSocket();
        return self::MESSAGE_PARSED;
    }
}