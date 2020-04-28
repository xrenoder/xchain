<?php
/**
 * Stack of tasks for server
 */
class Queue extends aBaseApp
{
    public function getServer() : Server {return $this->getParent();}

    private $tasks = array();
    /** @var int  */
    private $maxProp = 0;

    public static function create(Server $server) : self
    {
        return new self($server);
    }

    public function runOneTask() : bool
    {
        return $this->shift()->run();
    }

    public function runTopTasks() : void
    {
        $tasks = $this->shiftTopTasks();

        foreach($tasks as $task) {
            $task->run();
        }
    }

    public function push(aTask $task) : self
    {
        $priority = $task->getPriority();

        if (!isset($this->tasks[$priority])) {
            $this->tasks[$priority] = array();

            if ($priority > $this->maxProp) {
                $this->maxProp = $priority;
            }
        }

        $this->tasks[$priority][] = $task;

        return $this;
    }

    private function shift() : ?aTask
    {
        for ($i = 0; $i <= $this->maxProp; $i++) {
            if (!isset($this->tasks[$i]) || empty($this->tasks[$i])) {
                continue;
            }

            return array_shift($this->tasks[$i]);
        }

        return null;
    }

    private function shiftTopTasks() : ?array
    {
        for ($i = 0; $i <= $this->maxProp; $i++) {
            if (!isset($this->tasks[$i]) || empty($this->tasks[$i])) {
                continue;
            }

            return array_shift($this->tasks[$i]);
        }

        return null;
    }
}