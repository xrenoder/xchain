<?php


class AliveTask extends aTask
{
    /** @var int */
    protected $priority = 0; /* overrided */

    public function run() : bool
    {
        if (!$socket = $this->getSocket()) {
            return false;
        }

        $socket->addOutData(AliveReqMessage::createMessage());

        return true;
    }
}