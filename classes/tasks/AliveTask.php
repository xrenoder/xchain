<?php


class AliveTask extends aTask
{
    /** @var string  */
    protected $name = 'Alive Task'; /* overrided */

    /** @var int */
    protected $priority = 0; /* overrided */

    public function run() : bool
    {
        if (!$this->useSocket()) {
            return false;
        }

        $this->socket->addOutData(AliveReqMessage::createMessage());

        return true;
    }
}