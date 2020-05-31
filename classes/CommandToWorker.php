<?php


class CommandToWorker extends aCommand
{
    public const INCOMING_PACKET =  0;
    public const SOCKET_CLOSED =    1;
    public const NODE_CHANGED =     2;
    public const WORKER_MUST_DIE =  3;

    /** @var string[] */
    protected $handlers = array(    /* overrided */
        self::INCOMING_PACKET => 'incomingPacketHandler',   // $worker->incomingPacketHandler($socketId, $serializedLegate) : bool
    );

}