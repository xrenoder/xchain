<?php
/**
 * Socket
 */
class Socket extends aBase
{
    protected static $dbgLvl = Logger::DBG_SOCKET;

    public function getApp() : App {return $this->getLocator();}
    public function getServer() : Server {return $this->getParent();}

    /** @var resource */
    private $fd;
    public function setFd($val) : self {$this->fd = $val; return $this;}
    public function getFd() {return $this->fd;}

    /** @var string */
    private $id;
    public function setId(string $val) : self {$this->id = $val; return $this;}
    public function getId() : string {return $this->id;}

    /** @var bool  */
    private $blockMode = true;

    /** @var int  */
    private $time = null;
    public function setTime() : self {$this->time = $this->getServer()->getNowTime(); return $this;}
    public function getTime() : int {return $this->time;}

    /** @var SocketLegate */
    private $legate = null;
    public function setLegate(SocketLegate $val) : self {$this->legate = $val; return $this;}
    public function getLegate() : SocketLegate {return $this->legate;}

    /** @var int  */
    private $legatesInWorker = 0;
    public function getLegatesInWorker() : int {return $this->legatesInWorker;}

    /** @var bool  */
    private $needCloseAfterAllLegatesReturn = false;

    /** @var bool  */
    private $readyForCloseAllLegatesReturn = false;

    /** @var int */
    private $threadId = null;
    public function getThreadId() : ?int {return $this->threadId;}

    /** @var string  */
    private $outData = '';

    /** @var Host  */
    private $host;
    public function setHost(Host $val) : self {$this->host = $val; return $this;}
    public function getHost() : Host {return $this->host;}

    /** @var int  */ /* when busy - 0, when free - time of freedom moment */
    private $freeTime = 0;
    public function getFreeTime() : int {return $this->freeTime;}
    public function isFree() : bool {return $this->freeTime !== 0;}

    /** @var aTask  */
    private $task;
    public function setTask(aTask $val) : self {$this->task = $val; $this->legate->setMyNodeId($this->task->getPool()->getMyNodeId()); return $this;}
    public function unsetTask() : self {$this->task = null; $this->legate->setMyNodeId($this->getApp()->getMyNode()->getId()); return $this;}
    public function getTask() : ?aTask {return $this->task;}

    /**
     * @param Server $server
     * @param $fd
     * @param string $id
     * @param Host $host
     * @return self
     */
    public static function create(Server $server, Host $host, $fd, string $id) : self
    {
        $me = new self($server);

        $me->setLegate(SocketLegate::create($me, $id));
        $me->getLegate()->setMyNodeId($me->getLocator()->getMyNode()->getId());

        $me
            ->setHost($host)
            ->setFd($fd)
            ->setId($id)
            ->setTime()
            ->getServer()->setSocket($me, $id);

        return $me;
    }

    /**
     * Set socket to nonblocking mode
     * @param bool $mode
     * @return self
     */
    public function setBlockMode(bool $mode) : self
    {
        if ($this->blockMode === $mode) {
            return $this;
        }

        if (!stream_set_blocking($this->fd, $mode)) {
            $this->err('ERROR: cannot set block mode');
        }

        $this->blockMode = $mode;

        return $this;
    }

    /**
     * Set socket descriptor to server recvs array (for select)
     * @return self
     */
    public function setRecvs() : self
    {
        $this->getServer()->setRecvs($this->fd,$this->id);
        return $this;
    }

    /**
     * Unset socket descriptor in server recvs array
     * @return self
     */
    public function unsetRecvs() : self
    {
        $this->getServer()->unsetRecvs($this->id);
        return $this;
    }

    /**
     * Set socket descriptor to server sends array (for select)
     * @return self
     */
    public function setSends() : self
    {
        $this->getServer()->setSends($this->fd,$this->id);
        return $this;
    }

    /**
     * Unset socket descriptor in server sends array
     * @return self
     */
    public function unsetSends() : self
    {
        $this->getServer()->unsetSends($this->id);
        return $this;
    }


    /**
     * @return self
     */
    public function setBusy() : self
    {
        if ($this->legate->isConnected()) {
            $this->getServer()->busyConnected($this->host, $this->id);
        } else {
            $this->getServer()->busyAccepted($this->host, $this->id);
        }

        $this->freeTime = 0;
        $this->legate->setFreeAfterSend(false);

        return $this;
    }

    /**
     * @return self
     */
    public function setFree() : self
    {
// TODO если в ноду стучится клиент - после завершения операций соединение не сохраняется, а разрывается
        $this->freeTime = time();
        $this->legate->setFreeAfterSend(false);

        if ($this->legate->isConnected()) {
            $this->getServer()->freeConnected($this, $this->host, $this->id);
        } else {
            $this->getServer()->freeAccepted($this, $this->host, $this->id);
        }

        if ($this->task) {
            $this->task->finish();
        }

        $this->time = 0;

        return $this;
    }

    /**
     * Add packet to socket for sending
     * @param $message
     * @return self
     */
    public function sendMessage(?aMessage $message) : self
    {
        if ($message !== null) {
            $message->createRaw();
            $messageString = $message->getRaw();
        } else {
            $messageString = '';
        }

        return $this->sendMessageString($messageString);
    }

    public function sendMessageString(?string $messageString) : self
    {
        if ($messageString === null) {
            $messageString = '';
        }
        $this->outData .= $messageString;
        $this->setSends();

        return $this;
    }

    /**
     * Write data to socket
     * @return self
     */
    public function send() : self
    {
        $this->setTime();

        if ($this->outData) {
            if (DBG_ONEBYTE_SEND_USLEEP) {
                $buff = $this->outData[0];   // отладочное для побайтовой отправки пакетов
            } else {
                $buff = $this->outData;
            }

            if (($realLength = fwrite($this->fd, $buff, strlen($buff))) === false) {
                $this->err('ERROR: write to socket error');
            }

            if ($realLength) {
                $this->outData = substr($this->outData, $realLength);
            }

            $this->dbg('SEND ' . $this->id . ": $realLength bytes");
        } else {
            $this->dbg('SEND ' . $this->id . ": ZERO bytes, switch to received mode");
        }

        if (!$this->outData) {
            $this->time = 0;

            if ($this->legate->needCloseAfterSend()) {
                $this->dbg("Worker command: close socket after send");
                $this->close('by needCloseAfterSend');
            } else {
                $this
                    ->unsetSends()
                    ->setRecvs();

                if ($this->legate->needFreeAfterSend()) {
                    $this->setFree();
                }
            }
        }

        return $this;
    }

    /**
     * Read data from socket
     * @return bool
     */
    public function receive() : bool
    {
        $this->setTime();

        $needRead = -1;

        if ($bufferSize = $this->legate->getReadBufferSize()) {
            $needRead = $bufferSize;
            $this->legate->setReadBufferSize(0);
        }

        if (($data = stream_get_contents($this->fd, $needRead)) === false) {
            $this->err('ERROR: read from stream error');
        }

        if ($data === '')	{	// удаленный сервер разорвал соединение
            $this->dbg("Socket " . $this->getId(). " will be closed: remote side shutdown connection");

            if ($this->legatesInWorker !== 0) {
                $this->needCloseAfterAllLegatesReturn = true;
                $this
                    ->unsetRecvs()
                    ->unsetSends();
            } else {
                $this->close(" from remote side");
            }

            return false;
        }

        $this->dbg('RECV ' . $this->getId() . ': '. strlen($data) . ' bytes');

        $this->legate->setIncomingStringTime($this->getServer()->getNowTime());
// TODO включить обработку входящего пакета для защиты от зловредной инъекции
// TODO провести тщательное тестирование на возможность зловредной инъекции
        $this->legate->setIncomingString($data);

        /** @var App $app */
        $app = $this->getApp();

        if ($this->threadId === null) {
            $this->threadId = $app->getBestThreadId();
        }

        $app->incThreadBusy($this->threadId);
        $channel = $app->getChannelFromParent($this->threadId);

        $serializedLegate = $this->legate->serializeInSocket();
        CommandToWorker::send($channel, CommandToWorker::INCOMING_PACKET, $this->legate->getId(), $serializedLegate);
        $this->legatesInWorker++;

        $this->getApp()->dbg("Send legate from socket $this->id to worker $this->threadId");
//        $this->dbg("\n $serializedLegate\n");

        return false;
    }

    public function workerResponseHandler(string $serializedLegate) : bool
    {
        /** @var App $app */
        $app = $this->getApp();

//        $this->dbg("Socket legate from worker:\n $serializedLegate\n");

        $legate = $this->legate = $this->legate->unserializeInSocket($serializedLegate);
        $this->legatesInWorker--;

        if ($this->needCloseAfterAllLegatesReturn && $this->legatesInWorker === 0) {
            $this->readyForCloseAllLegatesReturn = true;
        }

        $result = $this->legate->getWorkerResult();

        if ($result === aMessage::MESSAGE_PARSED) {
            $this->dbg("Worker result: MESSAGE_PARSED");
        }

        if ($result === aMessage::MESSAGE_PARSED || $legate->isBadData() || $legate->needCloseSocket()) {
            $this->dbg("Free worker " . $this->threadId);
            $app->decThreadBusy($this->threadId);
            $this->threadId = null;
            $this->time = 0;
        }

        if ($legate->isBadData()) {
            $this->dbg("Worker command: bad data");
            $this->badData();
        } else if ($this->readyForCloseAllLegatesReturn || $legate->needCloseSocket()) {
            if ($this->readyForCloseAllLegatesReturn) {
                if ($legate->needCloseSocket()) {
                    $this->dbg("Worker command: close socket");
                    $addLogStr = " and by worker command";
                } else {
                    $addLogStr = "";
                }

                $this->close(" from remote side" . $addLogStr);
            } else {
                $this->dbg("Worker command: close socket");
                $this->close(' by worker command');
            }
        } else if ($legate->getResponseString() !== null) {
            $this->dbg("Worker command: send response");
            $messageString = $legate->getResponseString();
            $legate->setResponseString(null);
            $this->sendMessageString($messageString);
        } else {
            $this->dbg("Worker command: nothing to do");
        }

        return $result;
    }

    /**
     * Close socket
     * @return self
     */
    public function close($logStr = '') : self
    {
        stream_socket_shutdown($this->fd, STREAM_SHUT_RDWR);
        fclose($this->fd);

        $this
            ->unsetRecvs()
            ->unsetSends();

        $host = $this->getHost();
        $id = $this->getId();

        $this->getServer()
             ->unsetSocket($id)
             ->busyAccepted($host, $id)
             ->busyConnected($host, $id);

        unset($this->task);

        if ($this->threadId !== null) {
            $channel = $this->getApp()->getChannelFromParent($this->threadId);
            CommandToWorker::send($channel, CommandToWorker::SOCKET_CLOSED, $this->legate->getId());
            $this->log("Message about closing socket $this->id to thread $this->threadId sended");
        }

        $this->dbg("Socket $id closed" . $logStr);

        return $this;
    }

    public function badData() : void
    {
// TODO продумать действия при закрытии сокета, на который поступили плохие данные
// например, закрыть все свободные сокеты, соединенные с этим хостом
        $this->close(" cause bad data");
    }
}