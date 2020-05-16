<?php
/**
 * Socket
 */
class Socket extends aBase
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

//    /** @var bool  */
//    private $keepAlive = false;

    /** @var int  */
    private $time = null;
    public function setTime() : self {$this->time = time(); return $this;}

    /** @var aMessage  */
    private $inMessage = null;
    public function setInMessage(aMessage $val) : self {$this->inMessage = $val; return $this;}
    public function cleanInMessage() : self {$this->inMessage = null; return $this; }
    public function getInMessage() : ?aMessage {return $this->inMessage;}

    /** @var string  */
    private $outData = '';

    /** @var aMessage  */
    private $delayedOutMessage = null;

    /** @var Host  */
    private $host;
    public function setHost(Host $val) : self {$this->host = $val; return $this;}
    public function getHost() : Host {return $this->host;}

    /** @var bool  */
    private $freeAfterSend = false;
    public function setFreeAfterSend() : self {$this->freeAfterSend = true; return $this;}
    public function needFreeAfterSend() : bool {return $this->freeAfterSend;}

    /** @var bool  */
    private $closeAfterSend = false;
    public function setCloseAfterSend() : self {$this->closeAfterSend = true; return $this;}
    public function needCloseAfterSend() : bool {return $this->closeAfterSend;}

    /** @var int  */ /* when busy - 0, when free - time of freedom moment */
    private $freeTime = 0;
    public function getFreeTime() : int {return $this->freeTime;}
    public function isFree() : bool {return $this->freeTime !== 0;}

    /* is this socket create by 'connect' */
    /** @var bool  */
    private $connected = false;
    public function setConnected() : self {$this->connected = true; return $this;}
    public function isConnected() : bool {return $this->connected;}

    /** @var aTask  */
    private $task;
    public function setTask(aTask $val) : self {$this->task = $val; return $this;}
    public function unsetTask() : self {$this->task = null; return $this;}
    public function getTask() : ?aTask {return $this->task;}

    /** @var bool  */
    private $needAliveCheck = true;

    /** @var bool  */
    private $isAliveChecked = false;
    public function setAliveChecked() : self {$this->isAliveChecked = true; return $this;}
    public function isAliveChecked() : bool {return $this->isAliveChecked;}

    /** @var bool  */
    private $isServerBusy = false;
    public function setServerBusy() : self {$this->isServerBusy = true; return $this;}
    public function isServerBusy() : bool {return $this->isServerBusy;}

    /** @var aNode  */
    private $remoteNode = null;
    public function setRemoteNode(aNode $val) : self {$this->remoteNode = $val; return $this;}
    public function getRemoteNode() : ?aNode {return $this->remoteNode;}

    /** @var bool  */
    private $areNodesCompatible = null;
    public function areNodesCompatible() : ?bool {return $this->areNodesCompatible;}

    /** @var string  */
    private $remoteAddrBin = null;
    public function setRemoteAddrBin(string $val) : self {$this->remoteAddrBin = $val; return $this;}
    public function getRemoteAddrBin() : ?string {return $this->remoteAddrBin;}

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
        if ($this->connected) {
            $this->getServer()->busyConnected($this->host, $this->key);
        } else {
            $this->getServer()->busyAccepted($this->host, $this->key);
        }

        $this->freeTime = 0;
        $this->freeAfterSend = false;

        return $this;
    }

    /**
     * @return self
     */
    public function setFree() : self
    {
        $this->freeTime = time();
        $this->freeAfterSend = false;

        if ($this->connected) {
            $this->getServer()->freeConnected($this, $this->host, $this->key);
        } else {
            $this->getServer()->freeAccepted($this, $this->host, $this->key);
        }

        if ($this->task) {
            $this->task->finish();
        }

        $this->cleanInMessage();

        $this->remoteNode = null;

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
        if ($this->needAliveCheck && $this->isConnected()) {
            $this->needAliveCheck = false;
            $this->delayedOutMessage = $message;

            $message = AliveReqMessage::create($this, []);
        }

        if ($message !== null) {
            $this->outData .= $message->createMessageString();
        }

        $this->setSends();

        return $this;
    }

    /**
     * @return self
     */
    public function sendDelayedOutMessage() : self
    {
        $message = $this->delayedOutMessage;
        $this->delayedOutMessage = null;

        return $this->sendMessage($message);
    }

    public function getMyNodeId() : int
    {
        if ($this->task) {
            return $this->task->getPool()->getMyNodeId();
        }

        return $this->getApp()->getMyNode()->getId();
    }

    public function checkNodesCompatiblity() : void
    {
        $myNodeId = $this->getMyNodeId();

        if($this->isConnected()) {
            $myCriteria = NodeClassEnum::getCanConnect($myNodeId);
            $logTxt = "cannot connect to";
        } else {
            $myCriteria = NodeClassEnum::getCanAccept($myNodeId);
            $logTxt = "cannot accept connection from";
        }

        $result = $myCriteria & $this->remoteNode->getId();

        $this->areNodesCompatible = ($result !== 0);

        if (!$this->areNodesCompatible) {
            $this->dbg('Nodes uncompatible: ' . NodeClassEnum::getName($myNodeId) . " $logTxt " . $this->remoteNode->getName());
        }
    }

    /**
     * Write data to socket
     * @return self
     */
    public function send() : self
    {
        $this->setTime();

        if ($this->outData) {
            $buff = $this->outData;
//            $buff = $this->outData[0];

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
            if ($this->needCloseAfterSend()) {
                $this->close();
            } else {
                $this
                    ->unsetSends()
                    ->setRecvs();

                if ($this->needFreeAfterSend()) {
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

        if ($this->inMessage && $this->inMessage->getDeclaredLen()) {
            $needRead = $this->inMessage->getBufferSize();
        }

        if (($data = stream_get_contents($this->fd, $needRead)) === false) {
            $this->err('ERROR: read from stream error');
        }

        if (!$data)	{	// удаленный сервер разорвал соединение
            $this->close(" from remote side");
            return false;
        }

        $this->dbg('RECV ' . $this->getKey() . ': '. strlen($data) . ' bytes');

        return aMessage::parser($this, $data);
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

    /**
     * @return bool
     */
    public function badData() : bool
    {
// TODO продумать действия при закрытии сокета, на который поступили плохие данные
// например, закрыть все свободные сокеты, соединенные с этим хостом
        $this->close();
        return false;
    }
}