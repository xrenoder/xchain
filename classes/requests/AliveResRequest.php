<?php


class AliveResRequest extends Request
{
    protected function handler(): bool
    {
        $this->dbg(Logger::DBG_REQ,'Alive response');
        return true;
    }

    public static function createMessage(): string
    {
        $type = pack(static::FLD_TYPE_FMT, RequestEnum::ALIVE_RES);
        $len = strlen($type) + Request::FLD_LENGTH_LEN;
        $mess = pack(Request::FLD_LENGTH_FMT, $len) . $type;

        return $mess;
    }
}