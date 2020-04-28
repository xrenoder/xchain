<?php
/**
 * Socket
 */
class Socket extends aBaseApp
{
    protected static $dbgLvl = Logger::DBG_SOCK;

    public function getServer() : Server {return $this->getParent();}

    private $fd;
    public function setFd($val) {$this->fd = $val; return $this;}
    public function getFd() {return $this->fd;}

    private $key;
    public function setKey($val) {$this->key = $val; return $this;}
    public function getKey() {return $this->key;}

    /** @var bool  */
    private $blockMode = true;

    /** @var bool  */
    private $keepAlive = false;

    /** @var int  */
    private $time = null;
    public function setTime() {$this->time = time(); return $this;}

    /** @var string  */
    private $messageStr = '';
    public function addMessageStr($val) {$this->messageStr .= $val; return $this;}
    public function getMessageStr() {return $this->messageStr;}

    /** @var aMessage  */
    private $message;
    public function setMessage($val) {$this->message = $val; return $this;}
    public function getMessage() {return $this->message;}

    /** @var string  */
    private $outData = '';
    public function setOutData($val) {$this->outData = $val; return $this;}
    public function getOutData() {return $this->outData;}

    /** @var Host  */
    private $host;
    public function setHost($val) {$this->host = $val; return $this;}
    public function getHost() {return $this->host;}

    /** @var bool  */
    private $freeAfterSend = false;
    public function setFreeAfterSend() {$this->freeAfterSend = true; return $this;}
    public function needFreeAfterSend() {return $this->freeAfterSend;}

    /** @var int  */ /* when busy - 0, when free - time of freedom moment */
    private $freeTime = 0;
    public function getFreeTime() {return $this->freeTime;}
    public function isFree() {return $this->freeTime !== 0;}

    /* is this socket create by 'connect' */
    /** @var bool  */
    private $connected = false;
    public function setConnected() {$this->connected = true; return $this;}
    public function isConnected() {return $this->connected;}

    /**
     * @param Server $server
     * @param $fd
     * @param string $key
     * @param Host $host
     * @return Socket
     */
    public static function create(Server $server, Host $host, $fd, string $key): Socket
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
     * @return Socket
     */
    public function setBlockMode(bool $mode): Socket
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
     * @return $this
     */
    public function setRecvs(): Socket
    {
        $this->getServer()->setRecvs($this->fd,$this->getKey());
        return $this;
    }

    /**
     * Unset socket descriptor in server recvs array
     * @return $this
     */
    public function unsetRecvs(): Socket
    {
        $this->getServer()->unsetRecvs($this->getKey());
        return $this;
    }

    /**
     * Set socket descriptor to server sends array (for select)
     * @return $this
     */
    public function setSends(): Socket
    {
        $this->getServer()->setSends($this->fd,$this->getKey());
        return $this;
    }

    /**
     * Unset socket descriptor in server sends array
     * @return $this
     */
    public function unsetSends(): Socket
    {
        $this->getServer()->unsetSends($this->getKey());
        return $this;
    }

    public function setFree()
    {
        $this->freeTime = time();
        $this->freeAfterSend = false;

        if ($this->connected) {
            $this->getServer()->setUnused($this, $this->getHost(), $this->getKey());
        }

        return $this;
    }

    public function setBusy()
    {
        if ($this->connected) {
            $this->getServer()->unsetUnused($this->getHost(), $this->getKey());
        }

        $this->freeTime = 0;
        $this->freeAfterSend = false;

        return $this;
    }

    /**
     * Write data to socket
     * @return Socket
     */
    public function send(): Socket
    {
        $this->setTime();
        $buff = $this->getOutData();

        if (($realLength = fwrite($this->fd, $buff, strlen($buff))) === false) {
            $this->err('ERROR: write to socket error');
        }

        if ($realLength) {
            $this->setOutData(substr($this->getOutData(), $realLength));
        }

        $this->dbg(static::$dbgLvl, 'SEND ' . $this->getKey() . ": $realLength bytes");

        if (!$this->getOutData()) {
            $this
                ->unsetSends()
                ->setRecvs();

            if ($this->needFreeAfterSend()) {
                $this->setFree();
            }
        }

        return $this;
    }

    /**
     * Read data from socket
     * @param $key
     * @return bool
     */
    public function receive(): bool
    {
        $this->setTime();

        if (($data = stream_get_contents($this->fd)) === false) {
            $this->err('ERROR: read from stream error');
        }

        if (!$data)	{	// удаленный сервер разорвал соединение
            $this->close();
            return false;
        }

        $this->dbg(static::$dbgLvl, 'RECV ' . $this->getKey() . ': '. $data);

        return aMessage::parser($this, $data);
    }

    /**
     * Add packet to socket for sending
     * @param $data
     * @return Socket
     */
    public function addOutData($data): Socket
    {
        $this->outData .= $data;
        $this->setSends();

        return $this;
    }

    /**
     * Close socket
     */
    public function close(): void
    {
        stream_socket_shutdown($this->fd, STREAM_SHUT_RDWR);
        fclose($this->fd);

        $this
            ->unsetRecvs()
            ->unsetSends()
            ->getServer()->unsetSocket($this->getKey());

        $this->dbg(static::$dbgLvl, 'Socket ' . $this->getKey() . ' closed');
    }

    public function badData(): bool
    {
// TODO продумать действия при закрытии сокета, на который поступили плохие данные
        $this->close();
        return false;
    }
}