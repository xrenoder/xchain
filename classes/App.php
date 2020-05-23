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

    /** @var Runtime[]  */
    private $threads = array();
    public function setThread(string $id, Runtime $val) : self {$this->threads[$id] = $val; $this->setThreadBusy($id, 0); return $this;}
    public function getThread(string $id) : Runtime {return $this->threads[$id];}

    /** @var int[]  */
    private $threadsBusy = array();
    public function setThreadBusy(string $id, int $val) : self {$this->threadsBusy[$id] = $val; return $this;}
    public function incThreadBusy(string $id) : self {$this->threadsBusy[$id]++; return $this;}
    public function decThreadBusy(string $id) : self {$this->threadsBusy[$id]--; return $this;}
    public function getBestThreadId() : string {asort($this->threadsBusy); return array_key_first($this->threadsBusy);}

    /** @var Channel[]  */
    private $channelsFromSocket = array();
    public function setChannelFromSocket(string $id, Channel $val) : self {$this->channelsFromSocket[$id] = $val; return $this;}
    public function getChannelFromSocket(string $id) : Channel {return $this->channelsFromSocket[$id];}

    /** @var Channel[]  */
    private $channelsFromWorker = array();
    public function setChannelFromWorker(string $id, Channel $val) : self {$this->channelsFromWorker[$id] = $val; return $this;}
    public function getChannelFromWorker(string $id) : Channel {return $this->channelsFromWorker[$id];}

    /** @var Events  */
    private $events = null;
    public function setEvents(Events $val) : self {$this->events = $val; return $this;}
    public function getEvents() : Events {return $this->events;}

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
