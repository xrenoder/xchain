<?php


class AliveTask extends aTask
{
    /** @var string  */
    protected $name = 'Alive Task'; /* overrided */

    /** @var int */
    protected $priority = 0; /* overrided */

    protected function customRun() : bool
    {
        if (!$this->useSocket()) {
            $this->dbg(static::$dbgLvl,$this->name . ' cannot get socket');
            return false;
        }

        $this->socket->addOutData(AliveReqMessage::createMessage());

        return true;
    }

    protected function customFinish()
    {

    }
}