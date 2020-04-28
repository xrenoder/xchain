<?php
/**
 * Request "Is daemon alive?"
 */
class AliveReqMessage extends aMessage
{
    /** @var int  */
    protected static $enumId = MessageEnum::ALIVE_REQ;  /* overrided */
    /** @var string */
    protected static $name = 'AliveRequest Message';    /* overrided */

    public static function createMessage(): string
    {
        $type = pack(static::FLD_TYPE_FMT, static::$enumId);
        $len = strlen($type) + static::FLD_LENGTH_LEN;
        $mess = pack(static::FLD_LENGTH_FMT, $len) . $type;

        return $mess;
    }

    protected function handler(): bool
    {
        $this->dbg(static::$dbgLvl,static::$name .  ' detected');
        $this->getSocket()->addOutData(AliveResMessage::createMessage());
        $this->getSocket()->setFreeAfterSend();
        return false;
    }
}