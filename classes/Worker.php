<?php


class Worker extends aLocator implements constMessageParsingResult
{
    /** @var SummaryDataSet */
    private $summaryDataSet;
    public function setSummaryDataSet(SummaryDataSet $val) : self {$this->summaryDataSet = $val; return $this;}
    public function getSummaryDataSet() : SummaryDataSet {return $this->summaryDataSet;}

    /** @var SocketLegate[]  */
    private $legates = array();
    public function setLegate(string $id, SocketLegate $val) : self {$this->legates[$id] = $val; return $this;}
    public function unsetLegate(string $id) : self {unset($this->legates[$id]); return $this;}
    public function getLegate(string $id) : ?SocketLegate {return ($this->legates[$id] ?? null);}

    /**
     * Worker constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($this);
        $this->setName($name);
    }

    public function run(Channel $channelRecv, Channel $channelSend) : void
    {
        $this->dbg("Worker " . $this->getName() . " started");

        while(true) {
            [$legateId, $serializedLegate] = $channelRecv->recv();

            if (!isset($this->legates[$legateId])) {
                $this->setLegate($legateId,  SocketLegate::create($this, $legateId));
            }

            $this->setLegate($legateId, $this->getLegate($legateId)->unserializeInWorker($serializedLegate));

            $legate = $this->legates[$legateId];

            $legate->messageHandler($channelSend);

            if ($legate->getWorkerResult() === self::MESSAGE_PARSED || $legate->isBadData() || $legate->needCloseSocket()) {
                $this->unsetLegate($legateId);
// TODO продумать более тщательно сборку мусора в воркерах
                $this->garbageCollect();
            }
        }
    }
}