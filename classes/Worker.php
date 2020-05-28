<?php

class Worker extends aLocator implements constMessageParsingResult
{
    public function isWorker() : bool {return true;}

    /** @var SocketLegate[]  */
    private $legates = array();

    public function run(parallel\Channel $channelRecv, parallel\Channel $channelSend) : void
    {
        $this->log("Worker " . $this->getName() . " started");

        while(true) {
// TODO добавить команду о смене ноды в aLocator
// TODO добавить команду остановки воркера
// TODO добавить команду синхронизации данных из БД
            [$socketId, $serializedLegate] = $channelRecv->recv();

            if (!isset($this->legates[$socketId])) {
                $this->legates[$socketId] = SocketLegate::create($this, $socketId);
                $this->dbg("Worker " . $this->getName() . " attach legate from $socketId");
            }

            $this->legates[$socketId] = $this->legates[$socketId]->unserializeInWorker($serializedLegate);

            $this->legates[$socketId]->messageHandler($channelSend);

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
        }
    }
}