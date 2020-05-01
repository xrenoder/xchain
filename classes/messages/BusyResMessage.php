<?php
/**
 * Response "Daemon is alive"
 */
class BusyResMessage extends aMessage
{
    /** @var int  */
    protected static $enumId = MessageClassEnum::BUSY_RES;  /* overrided */
    /** @var string */
    protected static $name = 'BusyResponse';    /* overrided */

    protected static $needAliveCheck = false;

    /**
     * @return string
     */
    public static function createMessage() : string
    {
        $type = pack(static::FLD_TYPE_FMT, static::$enumId);
        $len = strlen($type) + static::FLD_LENGTH_LEN;
        $mess = pack(static::FLD_LENGTH_FMT, $len) . $type;

        return $mess;
    }

    /**
     * @return bool
     */
    protected function incomingMessageHandler() : bool
    {
//        $this->getSocket()->setFree();

        $this->getSocket()->close();

        return true;
    }
}