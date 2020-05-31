<?php

class Worker extends aLocator implements constMessageParsingResult
{
    public function isWorker() : bool {return true;}

    private $channelSend;

    /** @var SocketLegate[]  */
    private $legates = array();

    public function run(parallel\Channel $channelRecv, parallel\Channel $channelSend) : void
    {
        $this->log("Worker " . $this->getName() . " started");

        $this->channelSend = $channelSend;

        while(true) {
            $serializedCommand = $channelRecv->recv();
            CommandToWorker::handle($this, $serializedCommand);
        }
    }

    public function incomingPacketHandler(string $socketId, string $serializedLegate) : bool
    {
        if (!isset($this->legates[$socketId])) {
            $this->legates[$socketId] = SocketLegate::create($this, $socketId);
            $this->dbg("Worker " . $this->getName() . " attach legate from $socketId");
        }

        $this->legates[$socketId] = $this->legates[$socketId]->unserializeInWorker($serializedLegate);

        $this->legates[$socketId]->incomingPacketHandler($this->channelSend);

        if (
            $this->legates[$socketId]->getWorkerResult() === self::MESSAGE_PARSED
            || $this->legates[$socketId]->isBadData()
            || $this->legates[$socketId]->needCloseSocket()
        ) {
            unset($this->legates[$socketId]);
            $this->dbg("Worker " . $this->getName() . " unattach legate from $socketId");
// TODO продумать более тщательно сборку мусора в воркерах
            $this->garbageCollect();
        }

        return true;
    }
}