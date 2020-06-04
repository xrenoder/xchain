<?php


class CommandToWorker extends aCommand
{
    public const INCOMING_PACKET =  0;
    public const MUST_DIE_SOFT =    1;
    public const MUST_DIE_HARD =    2;
    public const SOCKET_CLOSED =    3;
    public const NODE_CHANGED =     4;

    /** @var string[] */
    protected $handlers = array(    /* overrided */
        self::INCOMING_PACKET => 'serverIncomingPacketHandler',   // $worker->serverIncomingPacketHandler($socketId, $serializedLegate) : bool
        self::MUST_DIE_SOFT => 'serverMustDieSoftHandler',        // $worker->serverMustDieSoftHandler(null, null) : bool
        self::MUST_DIE_HARD => 'serverMustDieHardHandler',        // $worker->serverMustDieHardHandler(null, null) : bool
        self::SOCKET_CLOSED => 'serverSocketClosedHandler',        // $worker->serverSocketClosedHandler($socketId, null) : bool
    );
}