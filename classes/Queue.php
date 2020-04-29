<?php
/**
 * Stack of tasks for server
 */
class Queue extends aBaseApp
{
    public function getServer() : Server {return $this->getParent();}

    private $pools = array();

    /** @var int  */
    private $maxProp = 0;

    public static function create(Server $server) : self
    {
        return new self($server);
    }

    public function addPool(TaskPool $pool) : self
    {
        $priority = $pool->getPriority();

        if (!isset($this->pools[$priority])) {
            $this->pools[$priority] = array();

            if ($priority > $this->maxProp) {
                $this->maxProp = $priority;
            }
        }

        $this->pools[$priority][] = $pool;

        return $this;
    }

    public function runOnePool() : bool
    {
        for ($i = 0; $i <= $this->maxProp; $i++) {
            if (!isset($this->pools[$i]) || empty($this->pools[$i])) {
                continue;
            }

            $pool = array_shift($this->pools[$i]);

            return $pool->run();
        }

        return false;
    }

    public function runTopPools() : void
    {
        $pools = array();

        for ($i = 0; $i <= $this->maxProp; $i++) {
            if (!isset($this->pools[$i]) || empty($this->pools[$i])) {
                continue;
            }

            $pools = array_shift($this->pools[$i]);
            break;
        }

        foreach($pools as $pool) {
            $pool->run();
        }
    }
}