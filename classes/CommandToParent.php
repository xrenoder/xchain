<?php


class CommandToParent extends aCommand
{
    public const PACKET_RESPONSE = 0;
    public const NODE_CHANGED =    1;
    public const WORKER_STOPPED =  2;
    public const NEED_NEW_BLOCKS = 3;

    /** @var string[] */
    protected $handlers = array(    /* overrided */
        self::PACKET_RESPONSE => 'packetResponseHandler',   // $server->packetResponseHandler($socketId, $serializedLegate) : bool
    );
}