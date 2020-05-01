<?php
/**
 * Request "Is daemon alive?"
 */
class AliveReqMessage extends aMessage
{
    /** @var int  */
    protected static $enumId = MessageClassEnum::ALIVE_REQ;  /* overrided */
    /** @var string */
    protected static $name = 'AliveRequest';    /* overrided */

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
        if ($this->getSocket()->isServerBusy()) {
            $this->getSocket()->addOutData(BusyResMessage::createMessage());
            $this->getSocket()->setCloseAfterSend();
        } else {
            $this->getSocket()->addOutData(AliveResMessage::createMessage());
            $this->getSocket()->setAliveChecked();
            $this->getSocket()->cleanMessage();
        }

//        $this->getSocket()->setFreeAfterSend();
        return false;
    }
}