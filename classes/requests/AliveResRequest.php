<?php


class AliveResRequest extends Request
{
    protected static $enum = RequestEnum::ALIVE_RES;

    protected function handler(): bool
    {
        $this->dbg(Logger::DBG_REQ,'Alive response');
        return true;
    }

    public static function createMessage(): string
    {
        $type = pack(static::FLD_TYPE_FMT, static::$enum);
        $len = strlen($type) + Request::FLD_LENGTH_LEN;
        $mess = pack(Request::FLD_LENGTH_FMT, $len) . $type;

        return $mess;
    }
}