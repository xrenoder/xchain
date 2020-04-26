<?php
/**
 * Request "Is daemon alive?"
 */

class AliveReqMessage extends aMessage
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
        $len = strlen($type) + static::FLD_LENGTH_LEN;
        $mess = pack(static::FLD_LENGTH_FMT, $len) . $type;

        return $mess;
    }
}