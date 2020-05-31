<?php


abstract class aCommand
{
    /** @var int */
    protected $command;

    /** @var string */
    protected $socketId;

    /** @var string */
    protected $data;

    /** @var string[] */
    protected $handlers = array(); /* override me */

    public function __construct(int $command, string $socketId, string $data)
    {
        $this->command = $command;
        $this->socketId = $socketId;
        $this->data = $data;
    }

    public static function handle(aBase $handlerObject, $serializedCommand) : bool
    {
        /** @var aCommand $commandObject */
        $commandObject = unserialize($serializedCommand, ['allowed_classes' => true]);
        return $commandObject->runHandler($handlerObject);
    }

    protected function runHandler(aBase $handlerObject) : bool
    {
        $handlerMethod = $this->handlers[$this->command];
        return $handlerObject->$handlerMethod($this->socketId, $this->data);
    }

    public function serialize() : string
    {
        return serialize($this);
    }
}