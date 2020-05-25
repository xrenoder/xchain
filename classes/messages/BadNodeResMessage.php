<?php
/**
 * Response "Daemon is alive, but your node cannot connect to me, socket will be closed"
 */
class BadNodeResMessage extends aMessage
{
    /** @var int  */
    protected static $id = MessageClassEnum::BAD_NODE_RES;  /* overrided */

    protected function incomingMessageHandler(): bool
    {
// TODO продумать действия при получении сообщения о нашем неверном обращении к другой ноде
        $this->getLegate()->setNeedCloseSocket();
        return self::MESSAGE_PARSED;
    }
}