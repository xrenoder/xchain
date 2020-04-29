<?php
/**
 * Base class for task classes
 */

abstract class aTask extends aBaseApp implements iTask
{
    protected static $dbgLvl = Logger::DBG_TASK;

    public function getPool() : TaskPool {return $this->getParent();}
    public function getQueue() : Queue {return $this->getPool()->getQueue();}
    public function getServer() : Server {return $this->getQueue()->getServer();}

    /** @var string */
    protected static $name = 'NotDeclaredTaskName'; /* override me */

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
     * @param TaskPool|null $pool when null - create pool with one task and same name as task
     * @param Host $host
     * @return iTask
     */
    public static function create(Server $server, TaskPool $pool, Host $host) : iTask
    {
        if (!$pool) {
            $queue = $server->getQueue();
            $pool = TaskPool::create($queue, static::$name);
        }

        $me = new static($pool);

        $me
            ->setHost($host)
            ->toPool();

        return $me;
    }

    public function toPool() : iTask
    {
        if ($this->isAdded) return $this;

        $this->isAdded = true;
        $this->getPool()->addTask($this);

        return $this;
    }

    public function run() : bool
    {
        if ($this->isRunned) return false;

        $this->dbg(static::$dbgLvl,static::$name . ' Task started');
        $this->isRunned = true;
        return $this->customRun();
    }

    public function finish()    /* Method called when task socket marked as "free" (called '->getSocket()->setFree()') */
    {
        $this->getSocket()->unsetTask();
        $this->customFinish();
        $this->isFinished = true;
        $this->getPool()->finishTask();
        $this->dbg(static::$dbgLvl,static::$name . ' Task finished');
    }

    protected function useSocket(): ?Socket
    {
        if ($this->socket) return $this->socket;
        if (!$this->host) return null;

        if ($this->socket = $this->getServer()->getFreeConnected($this->host)) {
            $this->socket->setBusy();
        } else if (!$this->socket = $this->getServer()->connect($this->host)) {
            return null;
        }

        $this->socket->setTask($this);

        return $this->socket;
    }
}