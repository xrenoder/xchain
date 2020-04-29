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

    protected function customRun() : bool
    {
        if (!$this->useSocket()) {
            $this->dbg(static::$dbgLvl,static::$name . ' Task cannot get socket');
            return false;
        }

//        $this->socket->addOutData(AliveReqMessage::createMessage());
        $this->socket->addOutData('');      // empty message will be not sended, but Socket before first message always send AliveRequest

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