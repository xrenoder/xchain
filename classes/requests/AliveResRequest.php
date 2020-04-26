<?php


class AliveResRequest extends Request
{
    protected static $enumId = RequestEnum::ALIVE_RES;

    protected function handler(): bool
    {
        $this->dbg(static::$dbgLvl,'Alive response detected');
        return true;
    }

    public static function createMessage(): string
    {
        $type = pack(static::FLD_TYPE_FMT, static::$enumId);
        $len = strlen($type) + Request::FLD_LENGTH_LEN;
        $mess = pack(Request::FLD_LENGTH_FMT, $len) . $type;

        return $mess;
    }
}