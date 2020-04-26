<?php


class AliveResMessage extends Message
{
    protected static $enumId = MessageEnum::ALIVE_RES;

    protected function handler(): bool
    {
        $this->dbg(static::$dbgLvl,'Alive response detected');
        return true;
    }

    public static function createMessage(): string
    {
        $type = pack(static::FLD_TYPE_FMT, static::$enumId);
        $len = strlen($type) + Message::FLD_LENGTH_LEN;
        $mess = pack(Message::FLD_LENGTH_FMT, $len) . $type;

        return $mess;
    }
}