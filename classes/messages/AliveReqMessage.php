<?php


class AliveReqMessage extends Message
{
    protected static $enumId = MessageEnum::ALIVE_REQ;

    protected function handler(): bool
    {
        $this->dbg(static::$dbgLvl,'Alive request detected');
        $this->getSocket()->addOutData(AliveResMessage::createMessage());
        return false;
    }

    public static function createMessage(): string
    {
        $type = pack(static::FLD_TYPE_FMT, static::$enumId);
//        $type = pack(static::FLD_TYPE_FMT, 3);
        $len = strlen($type) + Message::FLD_LENGTH_LEN;
        $mess = pack(Message::FLD_LENGTH_FMT, $len) . $type;

        return $mess;
    }
}