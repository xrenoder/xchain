<?php
/**
 * Base class for task classes
 */

abstract class aTask extends aBaseApp implements iTask
{
    protected static $dbgLvl = Logger::DBG_TASK;

    public function getQueue() : Queue {return $this->getParent();}
    public function getServer() : Server {return $this->getQueue()->getServer();}

    /** @var int */
    protected $priority; /* override me */
    public function getPriority() {return $this->priority;}

    /** @var Host */
    protected $host;
    public function setHost($val) : self {$this->host = $val; return $this;}
    public function getHost() : Host {return $this->host;}

    abstract public function run() : bool;

    public static function create(Queue $queue): self
    {
        return new static($queue);
    }

    public function queue() : self
    {
        $this->getQueue()->push($this);

        return $this;
    }

    protected function getSocket(): ?Socket
    {
        if (!$socket = $this->getUnusedSocket()) {
            if (!$socket = $this->getServer()->connect($this->getHost())) {
                return null;
            }
        }

        return $socket;
    }

    private function getUnusedSocket(): ?Socket
    {
        if ($socket = $this->getServer()->getUnused($this->getHost())) {
            $socket->setBusy();
            return $socket;
        }

        return null;
    }
}