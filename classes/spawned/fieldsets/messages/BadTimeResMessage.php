<?php
/**
 * Response "Daemon is alive, but node times are unsynchronized, socket will be closed"
 */
class BadTimeResMessage extends aMessage
{
    protected function incomingMessageHandler(): bool
    {
// TODO продумать действия при получении сообщения о рассинхронизации времени
// при достижении определенного числа сообщений от разных хостов технично отключиться (с записью в блокчейн)
// и отправить мыло владельцу ноды
// либо придумываем механизм автоматической коррекции времени

        $this->getLegate()->setNeedCloseSocket();
        return self::MESSAGE_PARSED;
    }
}