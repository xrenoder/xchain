<?php
/**
 * Response "Daemon is alive, but busy, cannot accept connections, socket will be closed"
 */
class BusyResMessage extends aMessage
{
    /** @var int  */
    protected static $id = MessageClassEnum::BUSY_RES;  /* overrided */
    protected static $needAliveCheck = false;           /* overrided */

    /**
     * @return bool
     */
    protected function incomingMessageHandler() : bool
    {
// TODO добавить обработку ответа Busy! от ноды (например, не стучаться в эту ноду в течение определенного времени)

        $this->getLegate()->setNeedCloseSocket();
        return self::MESSAGE_PARSED;
    }
}