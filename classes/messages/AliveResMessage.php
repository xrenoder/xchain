<?php
/**
 * Response "Daemon is alive"
 */
class AliveResMessage extends aMessage
{
    /** @var int  */
    protected static $enumId = MessageEnum::ALIVE_RES;  /* overrided */
    /** @var string */
    protected static $name = 'AliveResponse Message';    /* overrided */

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
        $this->getSocket()->setFree();
        return true;
    }
}