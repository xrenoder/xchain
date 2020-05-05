<?php
/**
 * DBA methods
 */

class DBA extends aBase
{
    protected static $dbgLvl = Logger::DBG_DBA;

    /**
     * @param Queue $queue
     * @param string $name
     * @return self
     */
    public static function create(Queue $queue, string $name) : self
    {
        $me = new static($queue);

        $me
            ->setName($name)
            ->setMyNodeId($me->getApp()->getMyNode()->getId());

        return $me;
    }
}