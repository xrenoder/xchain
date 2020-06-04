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

        $request
            = AliveReqMessage::create($this->getLocator())
                ->setData('test')
                ->setAuthorPublicKey($myPubKey);

        $this->socket->sendMessage($request);

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