<?php
/**
 * Base class for task classes
 */

abstract class aTask extends aBaseApp implements iTask
{
    protected static $dbgLvl = Logger::DBG_TASK;

    public function getQueue() : Queue {return $this->getParent();}
    public function getServer() : Server {return $this->getQueue()->getServer();}

    /** @var string */
    protected $name; /* override me */

    /** @var int */
    protected $priority; /* override me */
    public function getPriority() {return $this->priority;}

    /** @var Host */
    protected $host;
    public function setHost($val) : self {$this->host = $val; return $this;}
    public function getHost() : Host {return $this->host;}

    /** @var Socket */
    protected $socket = null;
    public function setSocket($val) : self {$this->socket = $val; return $this;}
    public function getSocket() : Socket {return $this->socket;}

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

    public function finish()
    {
        $this->dbg(static::$dbgLvl,$this->name . ' finished');
    }

    protected function useSocket(): ?Socket
    {
        if ($this->socket) return $this->socket;
        if ($this->host) return null;

        if ($this->socket = $this->getServer()->getFreeConnected($this->host)) {
            $this->socket->setBusy();
        } else if (!$this->socket = $this->getServer()->connect($this->host)) {
            return null;
        }

        $this->socket->setTask($this);

        return $this->socket;
    }
}