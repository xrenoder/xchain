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

    public function run(parallel\Channel $channelRecv, parallel\Channel $channelSend) : void
    {
        $this->log("Worker " . $this->getName() . " started");

        while(true) {
// TODO добавить команду о смене ноды в aLocator
// TODO добавить команду остановки воркера
            [$legateId, $serializedLegate] = $channelRecv->recv();

            if (!isset($this->legates[$legateId])) {
                $this->legates[$legateId] = SocketLegate::create($this, $legateId);
                $this->dbg("Worker " . $this->getName() . " attach legate from $legateId");
            }

            $newLegate = $this->legates[$legateId]->unserializeInWorker($serializedLegate);
            $this->legates[$legateId] = $newLegate;

            $this->legates[$legateId]->messageHandler($channelSend);

            if (
                $this->legates[$legateId]->getWorkerResult() === self::MESSAGE_PARSED
                || $this->legates[$legateId]->isBadData()
                || $this->legates[$legateId]->needCloseSocket()
            ) {
                $this->dbg("SocketLegate-object is:\n" . var_export($this->legates[$legateId], true) . "\n");

                $this->unsetLegate($legateId);
                $this->dbg("Worker " . $this->getName() . " unattach legate from $legateId");
// TODO продумать более тщательно сборку мусора в воркерах
                $this->garbageCollect();
            }
        }
    }
}