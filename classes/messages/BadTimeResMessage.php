<?php
/**
 * Response "Daemon is alive, but node times are unsynchronized, socket will be closed"
 */
class BadTimeResMessage extends aSimpleMessage
{
    /** @var int  */
    protected static $id = MessageClassEnum::BAD_TIME_RES;  /* overrided */
    protected static $needAliveCheck = false;               /* overrided */

    protected function incomingMessageHandler(): bool
    {
//        $this->getSocket()->setFree();

// TODO продумать действия при получении сообщения о рассинхронизации времени
// при достижении определенного числа сообщений от разных хостов технично отключиться (с записью в блокчейн)
// и отправить мыло владельцу ноды
// либо придумываем механизм автоматической коррекции времени

        $this->getSocket()->close();

        return true;
    }
}