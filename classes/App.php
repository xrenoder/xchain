<?php
/**
 * Application classenum
 */
class App extends aLocator
{
    public function isWorker() : bool {return false;}

    /** @var parallel\Runtime[]  */
    private $threads = array();
    public function setThread(string $id, parallel\Runtime $val) : self {$this->threads[$id] = $val; $this->setThreadBusy($id, 0); return $this;}
    public function getThread(string $id) : parallel\Runtime {return $this->threads[$id];}
    public function getAllThreads() : array {return $this->threads;}

    /** @var int[]  */
    private $threadsBusy = array();
    public function setThreadBusy(string $id, int $val) : self {$this->threadsBusy[$id] = $val; return $this;}
    public function incThreadBusy(string $id) : self {$this->threadsBusy[$id]++; return $this;}
    public function decThreadBusy(string $id) : self {$this->threadsBusy[$id]--; return $this;}
    public function getBestThreadId() : string {asort($this->threadsBusy); return array_key_first($this->threadsBusy);}

    /** @var parallel\Channel[]  */
    private $channelsFromParent = array();
    public function setChannelFromParent(string $id, parallel\Channel $val) : self {$this->channelsFromParent[$id] = $val; return $this;}
    public function getChannelFromParent(string $id) : parallel\Channel {return $this->channelsFromParent[$id];}

    /** @var parallel\Channel[]  */
    private $channelsFromWorker = array();
    public function setChannelFromWorker(string $id, parallel\Channel $val) : self {$this->channelsFromWorker[$id] = $val; return $this;}
    public function getChannelFromWorker(string $id) : ?parallel\Channel {return $this->channelsFromWorker[$id] ?? null;}

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

    public function unsetThread(string $id)
    {
        if (isset($this->threads[$id])) {
            unset($this->threads[$id]);
        }

        if (isset($this->threadsBusy[$id])) {
            unset($this->threadsBusy[$id]);
        }

        if (isset($this->channelsFromWorker[$id])) {
            unset($this->channelsFromWorker[$id]);
        }

        if (isset($this->channelsFromParent[$id])) {
            unset($this->channelsFromParent[$id]);
        }

        $this->log("Unset thread $id");
    }
}
