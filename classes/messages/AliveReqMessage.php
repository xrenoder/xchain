<?php
/**
 * Request "Is daemon alive?"
 */
class AliveReqMessage extends aMessage
{
    /** @var int  */
    protected static $enumId = MessageClassEnum::ALIVE_REQ;  /* overrided */
    /** @var string */
    protected static $name = 'AliveRequest Message';    /* overrided */

    protected static $needAliveCheck = false;

    public static function createMessage(): string
    {
        $type = pack(static::FLD_TYPE_FMT, static::$enumId);
        $len = strlen($type) + static::FLD_LENGTH_LEN;
        $mess = pack(static::FLD_LENGTH_FMT, $len) . $type;

        return $mess;
    }

    protected function incomingMessageHandler(): bool
    {
        $this->dbg(static::$dbgLvl,static::$name .  ' detected');

        if ($this->getSocket()->isServerBusy()) {
            $this->getSocket()->addOutData(BusyResMessage::createMessage());
            $this->getSocket()->setCloseAfterSend();
        } else {
            $this->getSocket()->addOutData(AliveResMessage::createMessage());
            $this->getSocket()->setAliveChecked();
            $this->getSocket()->cleanMessage();
        }

//        $this->getSocket()->setFreeAfterSend();
        return false;
    }
}