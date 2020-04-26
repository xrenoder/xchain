<?php


class AliveReqRequest extends Request
{
    protected static $enum = RequestEnum::ALIVE_REQ;

    protected function handler(): bool
    {
        $this->dbg(Logger::DBG_REQ,'Alive request');
        $this->socket->addOutData(AliveResRequest::createMessage());
        return false;
    }

    public static function createMessage(): string
    {
        $type = pack(static::FLD_TYPE_FMT, static::$enum);
        $len = strlen($type) + Request::FLD_LENGTH_LEN;
        $mess = pack(Request::FLD_LENGTH_FMT, $len) . $type;

        return $mess;
    }
}