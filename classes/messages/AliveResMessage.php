<?php
/**
 * Response "Daemon is alive"
 */

class AliveResMessage extends aMessage
{
    protected static $enumId = MessageEnum::ALIVE_RES;

    protected function handler(): bool
    {
        $this->dbg(static::$dbgLvl,'Alive response detected');
        $this->getSocket()->setFree();
        return true;
    }

    public static function createMessage(): string
    {
        $type = pack(static::FLD_TYPE_FMT, static::$enumId);
        $len = strlen($type) + static::FLD_LENGTH_LEN;
        $mess = pack(static::FLD_LENGTH_FMT, $len) . $type;

        return $mess;
    }
}