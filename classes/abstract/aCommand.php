<?php

abstract class aCommand
{
    /** @var int */
    protected $command;

    /** @var string */
    protected $targetId;    // socketId, threadId etc

    /** @var string */
    protected $data;

    /** @var string[] */
    protected $handlers = array(); /* override me */

    public function __construct(int $command, ?string $targetId, ?string $data)
    {
        $this->command = $command;
        $this->targetId = $targetId;
        $this->data = $data;
    }

    public static function send(parallel\Channel $channel, int $command, ?string $targetId = null, ?string $data = null)
    {
        $commandObject = new static($command, $targetId, $data);
        $channel->send(serialize($commandObject));
        unset($commandObject);
    }

    public static function handle(aBase $handlerObject, string $serializedCommand) : bool
    {
        /** @var aCommand $commandObject */
        $commandObject = unserialize($serializedCommand, ['allowed_classes' => true]);
        return $commandObject->runHandler($handlerObject);
    }

    protected function runHandler(aBase $handlerObject) : bool
    {
        $handlerMethod = $this->handlers[$this->command];
        return $handlerObject->$handlerMethod($this->targetId, $this->data);
    }
}