<?php
/**
 * Response "Daemon is alive"
 */
class AliveResMessage extends aMessage
{
    /** @var int  */
    protected static $enumId = MessageClassEnum::ALIVE_RES;  /* overrided */
    /** @var string */
    protected static $name = 'AliveResponse Message';    /* overrided */

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
//        $this->getSocket()->setFree();

        $this->getSocket()->setAliveChecked();
        $this->getSocket()->cleanMessage();
        $this->getSocket()->addDelayedOutData();

        return true;
    }
}