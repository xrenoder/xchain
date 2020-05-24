<?php
/**
 * Socket
 */
class Socket extends aBase implements constMessageParsingResult
{
    protected static $dbgLvl = Logger::DBG_SOCK;

    public function getServer() : Server {return $this->getParent();}

    /** @var resource */
    private $fd;
    public function setFd($val) : self {$this->fd = $val; return $this;}
    public function getFd() {return $this->fd;}

    /** @var string */
    private $key;
    public function setKey(string $val) : self {$this->key = $val; return $this;}
    public function getKey() : string {return $this->key;}

    /** @var bool  */
    private $blockMode = true;

    /** @var int  */
    private $time = null;
    public function setTime() : self {$this->time = time(); return $this;}

    /** @var SocketLegate */
    private $legate = null;
    public function setLegate(SocketLegate $val) : self {$this->legate = $val; return $this;}
    public function getLegate() : SocketLegate {return $this->legate;}

    /** @var bool  */
    private $legateInWorker = false;

    /** @var bool  */
    private $needCloseAfterLegateReturn = false;

    /** @var int */
    private $threadId = null;

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
    public function unsetTask() : self {$this->task = null; $this->legate->setMyNodeId($this->getLocator()->getMyNode()->getId()); return $this;}
    public function getTask() : ?aTask {return $this->task;}

    /**
     * @param Server $server
     * @param $fd
     * @param string $key
     * @param Host $host
     * @return self
     */
    public static function create(Server $server, Host $host, $fd, string $key) : self
    {
        $me = new self($server);

        $me->setLegate(SocketLegate::create($me, $key));
        $me->getLegate()->setMyNodeId($me->getLocator()->getMyNode()->getId());

        $me
            ->setHost($host)
            ->setFd($fd)
            ->setKey($key)
            ->setTime()
            ->getServer()->setSocket($me, $key);

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
        $this->getServer()->setRecvs($this->fd,$this->key);
        return $this;
    }

    /**
     * Unset socket descriptor in server recvs array
     * @return self
     */
    public function unsetRecvs() : self
    {
        $this->getServer()->unsetRecvs($this->key);
        return $this;
    }

    /**
     * Set socket descriptor to server sends array (for select)
     * @return self
     */
    public function setSends() : self
    {
        $this->getServer()->setSends($this->fd,$this->key);
        return $this;
    }

    /**
     * Unset socket descriptor in server sends array
     * @return self
     */
    public function unsetSends() : self
    {
        $this->getServer()->unsetSends($this->key);
        return $this;
    }


    /**
     * @return self
     */
    public function setBusy() : self
    {
        if ($this->legate->isConnected()) {
            $this->getServer()->busyConnected($this->host, $this->key);
        } else {
            $this->getServer()->busyAccepted($this->host, $this->key);
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
            $this->getServer()->freeConnected($this, $this->host, $this->key);
        } else {
            $this->getServer()->freeAccepted($this, $this->host, $this->key);
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
            $messageString = $message->createMessageString();
        } else {
            $messageString = '';
        }

        return $this->sendMessageString($messageString);
    }

    public function sendMessageString(string $messageString) : self
    {
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
//            $buff = $this->outData;
            $buff = $this->outData[0];   // отладочное для побайтовой отправки пакетов

            if (($realLength = fwrite($this->fd, $buff, strlen($buff))) === false) {
                $this->err('ERROR: write to socket error');
            }

            if ($realLength) {
                $this->outData = (substr($this->outData, $realLength));
            }

            $this->dbg('SEND ' . $this->key . ": $realLength bytes");
        } else {
            $this->dbg('SEND ' . $this->key . ": ZERO bytes, switch to received mode");
        }

        if (!$this->outData) {
            if ($this->legate->needCloseAfterSend()) {
                $this->close();
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

        if (!$data)	{	// удаленный сервер разорвал соединение
            $this->dbg("Socket " . $this->getKey(). " will be closed: remote side shutdown connection");

            if ($this->legateInWorker) {
                $this->needCloseAfterLegateReturn = true;
                $this
                    ->unsetRecvs()
                    ->unsetSends();
            } else {
                $this->close(" from remote side");
            }

            return false;
        }

        $this->dbg('RECV ' . $this->getKey() . ': '. strlen($data) . ' bytes');

        $this->legate->setIncomingBuffer($data);

        /** @var App $locator */
        $locator = $this->getLocator();

        if ($this->threadId === null) {
            $this->threadId = $locator->getBestThreadId();
        }

        $locator->incThreadBusy($this->threadId);
        $channel = $locator->getChannelFromSocket($this->threadId);

        $channel->send([$this->legate->getId(), $this->legate->serializeInSocket()]);

        $this->legateInWorker = true;

        return false;
    }

    public function workerResponseHandler($serializedLegate) {
        /** @var App $locator */
        $locator = $this->getLocator();

//        $this->dbg("Socket legate from worker:\n $serializedLegate\n");

        $legate = $this->legate = $this->legate->unserializeInSocket($serializedLegate);
        $this->legateInWorker = false;

        $result = $this->legate->getWorkerResult();

        $this->dbg("Worker result " . $result);

        if ($result === self::MESSAGE_PARSED || $legate->isBadData() || $legate->needCloseSocket()) {
            $this->dbg("Free worker " . $this->threadId);
            $locator->decThreadBusy($this->threadId);
            $this->threadId = null;
        }

        if ($legate->isBadData()) {
            $this->dbg("Worker command: bad data");
            $this->badData();
        } else if ($this->needCloseAfterLegateReturn || $legate->needCloseSocket()) {
            if ($this->needCloseAfterLegateReturn) {
                if ($legate->needCloseSocket()) {
                    $this->dbg("Worker command: close socket");
                    $addLogStr = " and by worker command";
                } else {
                    $addLogStr = "";
                }

                $this->close(" from remote side$addLogStr");
            } else {
                $this->dbg("Worker command: close socket");
                $this->close();
            }
        } else if ($legate->getResponseString() !== null) {
            $this->dbg("Worker command: send response");
            $messageString = $legate->getResponseString();
            $legate->setResponseString(null);
            $this->sendMessageString($messageString);
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
        $key = $this->getKey();

        $this->getServer()
             ->unsetSocket($key)
             ->busyAccepted($host, $key)
             ->busyConnected($host, $key);

        unset($this->task);

        $this->dbg("Socket $key closed" . $logStr);

        return $this;
    }

    public function badData() : void
    {
// TODO продумать действия при закрытии сокета, на который поступили плохие данные
// например, закрыть все свободные сокеты, соединенные с этим хостом
        $this->close(" cause bad data");
    }
}