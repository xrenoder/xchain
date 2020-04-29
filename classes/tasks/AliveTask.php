<?php


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

        $this->socket->addOutData(AliveReqMessage::createMessage());

        return true;
    }

    protected function customFinish()
    {

    }
}