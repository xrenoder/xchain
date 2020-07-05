<?php
/**
 * Response "Daemon is alive, but busy, cannot accept connections, socket will be closed"
 */
class BusyResMessage extends aMessage
{
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