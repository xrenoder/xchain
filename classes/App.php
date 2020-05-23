<?php
/**
 * Application class
 */
class App extends aLocator
{
    /** @var int */
    private $pid = null;
    public function setPid($val) : self {$this->pid = $val; return $this;}
    public function getPid() : int {return $this->pid;}

    /** @var parallel\Runtime[]  */
    private $threads = array();
    public function setThread(string $id, parallel\Runtime $val) : self {$this->threads[$id] = $val; $this->setThreadBusy($id, 0); return $this;}
    public function getThread(string $id) : parallel\Runtime {return $this->threads[$id];}

    /** @var int[]  */
    private $threadsBusy = array();
    public function setThreadBusy(string $id, int $val) : self {$this->threadsBusy[$id] = $val; return $this;}
    public function incThreadBusy(string $id) : self {$this->threadsBusy[$id]++; return $this;}
    public function decThreadBusy(string $id) : self {$this->threadsBusy[$id]--; return $this;}
    public function getBestThreadId() : string {asort($this->threadsBusy); return array_key_first($this->threadsBusy);}

    /** @var parallel\Channel[]  */
    private $channelsFromSocket = array();
    public function setChannelFromSocket(string $id, parallel\Channel $val) : self {$this->channelsFromSocket[$id] = $val; return $this;}
    public function getChannelFromSocket(string $id) : parallel\Channel {return $this->channelsFromSocket[$id];}

    /** @var parallel\Channel[]  */
    private $channelsFromWorker = array();
    public function setChannelFromWorker(string $id, parallel\Channel $val) : self {$this->channelsFromWorker[$id] = $val; return $this;}
    public function getChannelFromWorker(string $id) : parallel\Channel {return $this->channelsFromWorker[$id];}

    /** @var parallel\Events  */
    private $events = null;
    public function setEvents(parallel\Events $val) : self {$this->events = $val; return $this;}
    public function getEvents() : parallel\Events {return $this->events;}

    /** @var Daemon */
    private $daemon;
    public function setDaemon(Daemon $val) : self {$this->daemon = $val; return $this;}
    public function getDaemon() : Daemon {return $this->daemon;}

    /** @var Server */
    private $server;
    public function setServer(Server $val): self {$this->server = $val; return $this;}
    public function getServer() : Server {return $this->server;}

    /**
     * App constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($this);

        $this->setName($name);
        $this->pid = posix_getpid();
    }
}
