<?php
/**
 * Base class for task classes
 */

abstract class aTask extends aBase
{
    protected static $dbgLvl = Logger::DBG_TASK;

    public function getPool() : Pool {return $this->getParent();}
    public function getQueue() : Queue {return $this->getPool()->getQueue();}
    public function getServer() : Server {return $this->getQueue()->getServer();}

    /** @var string */
    protected static $name = 'NotDeclaredTaskName'; /* override me */
    public function getName() : string {return static::$name;}

    /** @var int */
    protected $priority = null; /* override me */
    public function getPriority() : ?int {return $this->priority;}

    /** @var Host */
    protected $host;
    public function setHost(Host $val) : self {$this->host = $val; return $this;}
    public function getHost() : Host {return $this->host;}

    /** @var Socket */
    protected $socket = null;
    public function setSocket($val) : self {$this->socket = $val; return $this;}
    public function getSocket() : Socket {return $this->socket;}

    /** @var bool  */
    private $isAdded = false;

    /** @var bool  */
    private $isRunned = false;

    /** @var bool  */
    private $isFinished = false;

    abstract protected function customRun() : bool;
    abstract protected function customFinish();

    /**
     * Create task and add it to pool
     * @param Server $server
     * @param Pool|null $pool when null - create pool with one task and same name as task
     * @param Host $host
     * @return aTask
     */
    public static function create(Server $server, ?Pool $pool, Host $host) : self
    {
        if (!$pool) {
            $queue = $server->getQueue();
            $pool = Pool::create($queue, static::$name);
        }

        $me = new static($pool);

        $me
            ->setHost($host)
            ->toPool();

        return $me;
    }

    /**
     * @return self
     */
    public function toPool() : self
    {
        if ($this->isAdded) {
            $this->dbg(static::$name . ' Task already added to Pool');
            return $this;
        }

        $this->isAdded = true;
        $this->getPool()->addTask($this);

        return $this;
    }

    /**
     * @return bool
     */
    public function run() : bool
    {
        if ($this->isFinished) {
            $this->dbg(static::$name . ' Task already finished, cannot start');
            return false;
        }

        if ($this->isRunned) {
            $this->dbg(static::$name . ' Task already started, cannot start');
            return false;
        }

        $this->dbg(static::$name . ' Task started');
        $this->isRunned = true;
        return $this->customRun();
    }

    public function finish() : void  /* Method called when task socket marked as "free" (called '->getSocket()->setFree()') */
    {
        $this->getSocket()->unsetTask();
        $this->customFinish();
        $this->isFinished = true;
        $this->dbg(static::$name . ' Task finished');
        $this->getPool()->finishTask();
    }

    /**
     * @return Socket|null
     */
    protected function useSocket() : ?Socket
    {
        if ($this->socket) return $this->socket;

        if (!$this->host) {
            $this->dbg(static::$name . ' Task cannot start without Host');
            return null;
        }

        if ($this->socket = $this->getServer()->getFreeConnected($this->host)) {
            $this->socket->setBusy();
        } else if (!$this->socket = $this->getServer()->connect($this->host)) {
            return null;
        }

        $this->socket->setTask($this);

        return $this->socket;
    }
}