<?php


class CommandToParent extends aCommand
{
    public const PACKET_RESPONSE =  0;
    public const IM_FINISH =        1;
    public const NODE_CHANGED =     2;
    public const NEED_NEW_BLOCKS =  3;


    /** @var string[] */
    protected $handlers = array(    /* overrided */
        self::PACKET_RESPONSE => 'workerPacketResponseHandler',   // $server->workerPacketResponseHandler($socketId, $serializedLegate) : bool
        self::IM_FINISH => 'workerImFinishHandler',   // $server->workerImFinishHandler($threadId, null) : bool
    );
}