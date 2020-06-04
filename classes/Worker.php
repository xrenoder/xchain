<?php

class Worker extends aLocator
{
    public function isWorker() : bool {return true;}

    private $channelSend;

    /** @var SocketLegate[]  */
    private $legates = array();

    private $legatesCounter = 0;

    private $mustDieLater = false;
    private $mustDieNow = false;

    public function run(parallel\Channel $channelRecv, parallel\Channel $channelSend) : void
    {
        $this->log("Worker " . $this->getName() . " started");

        $this->channelSend = $channelSend;

        while(true) {
            $serializedCommand = $channelRecv->recv();
            CommandToWorker::handle($this, $serializedCommand);

            if ($this->mustDieLater && $this->legatesCounter === 0) {
                $this->mustDieNow = true;
            }

            if ($this->mustDieNow) {
                break;
            }
        }

        CommandToParent::send($this->channelSend, CommandToParent::IM_FINISH, $this->getName());
        $this->log("Message about finishing thread " . $this->getName() . " sended");
        $this->log("Worker " . $this->getName() . " finished");
    }

    public function serverIncomingPacketHandler(string $socketId, string $serializedLegate) : bool
    {
        if (!isset($this->legates[$socketId])) {
            $this->legates[$socketId] = SocketLegate::create($this, $socketId);
            $this->legatesCounter++;
            $this->dbg("Worker " . $this->getName() . " attach legate from $socketId");
        }

        $this->legates[$socketId] = $this->legates[$socketId]->unserializeInWorker($serializedLegate);

        $this->legates[$socketId]->incomingPacketHandler($this->channelSend);

        if (
            $this->legates[$socketId]->getWorkerResult() === aMessage::MESSAGE_PARSED
            || $this->legates[$socketId]->isBadData()
            || $this->legates[$socketId]->needCloseSocket()
        ) {
            $this->serverSocketClosedHandler($socketId);
        }

        return true;
    }

    public function serverMustDieSoftHandler(?string $unusedOne = null, ?string $unusedTwo = null) : bool
    {
        $this->log("Worker " . $this->getName() . " will be soft finished");
        $this->mustDieLater = true;
        return true;
    }

    public function serverMustDieHardHandler(?string $unusedOne = null, ?string $unusedTwo = null) : bool
    {
        $this->log("Worker " . $this->getName() . " will be hard finished");
        $this->mustDieNow = true;
        return true;
    }

    public function serverSocketClosedHandler(string $socketId = null, ?string $unused = null) : bool
    {
        unset($this->legates[$socketId]);
        $this->legatesCounter--;

        $this->garbageCollect();

        if ($this->mustDieLater && $this->legatesCounter === 0) {
            $this->mustDieNow = true;
        }

        $this->dbg("Worker " . $this->getName() . " unattach legate from $socketId");

        return true;
    }
}