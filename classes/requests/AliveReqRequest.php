<?php


class AliveReqRequest extends Request
{
    protected static $enumId = RequestEnum::ALIVE_REQ;

    protected function handler(): bool
    {
        $this->dbg(static::$dbgLvl,'Alive request detected');
        $this->getSocket()->addOutData(AliveResRequest::createMessage());
        return false;
    }

    public static function createMessage(): string
    {
        $type = pack(static::FLD_TYPE_FMT, static::$enumId);
        $len = strlen($type) + Request::FLD_LENGTH_LEN;
        $mess = pack(Request::FLD_LENGTH_FMT, $len) . $type;

        return $mess;
    }
}