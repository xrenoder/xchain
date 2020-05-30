<?php
/**
 * Task "Is daemon alive?"
 */
class AliveTask extends aTask
{
    /** @var string  */
    protected static $name = 'Alive'; /* overrided */

    /** @var int */
    protected $priority = 0; /* overrided */

    /**
     * @return bool
     */
    protected function customRun() : bool
    {
        if (!$this->useSocket()) {
            $this->dbg(static::$name . ' Task cannot get socket');
            return false;
        }

        $myPubKey = $this->getLocator()->getMyAddress()->getPublicKeyBin();

        $this->socket->sendMessage(AliveReqMessage::create($this->getLocator(), [self::DATA => 'test', self::AUTHKEY => $myPubKey]));

        return true;
    }

    protected function customFinish()
    {

    }

/*
    public static function poolFinishHandler(array $data) : void
    {
        echo var_export($data, true);
    }
*/
}