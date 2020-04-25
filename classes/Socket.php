<?php
/**
 * Socket work
 */
class Socket extends AppBase
{
    /** @var Server */
    private $server;
    public function setServer($val) {$this->server = $val; return $this;}
    public function getServer() {return $this->server;}

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
    public function setTime($val) {$this->time = $val; return $this;}
    public function getTime() {return $this->time;}

    /** @var string  */
    private $inData = '';
    public function setInData($val) {$this->inData = $val; return $this;}
    public function addInData($val) {$this->inData .= $val; return $this;}
    public function getInData() {return $this->inData;}

    /** @var string  */
    private $requestStr = '';
    public function setRequestStr($val) {$this->requestStr = $val; return $this;}
    public function addRequestStr($val) {$this->requestStr .= $val; return $this;}
    public function getRequestStr() {return $this->requestStr;}

    /** @var Request  */
    private $request;
    public function setRequest($val) {$this->request = $val; return $this;}
    public function getRequest() {return $this->request;}

    private $requestLen;

    /** @var string  */
    private $outData = '';
    public function setOutData($val) {$this->outData = $val; return $this;}
    public function getOutData() {return $this->outData;}

    /** @var Host  */
    private $host;
    public function setHost($val) {$this->host = $val; return $this;}
    public function getHost() {return $this->host;}

    /**
     * @param Server $server
     * @param $fd
     * @param string $key
     * @param Host $host
     * @return Socket
     */
    public static function create(Server $server, Host $host, $fd, string $key): Socket
    {
        $me = new self($server->getApp());

        $me
            ->setServer($server)
            ->setHost($host)
            ->setFd($fd)
            ->setKey($key)
            ->setTime(time())
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

    /**
     * Write data to socket
     * @return Socket
     */
    public function write(): Socket
    {
        $buff = $this->getOutData();

        if (($realLength = fwrite($this->fd, $buff, strlen($buff))) === false) {
            $this->err('ERROR: write to socket error');
        }

        if ($realLength) {
            $this->setOutData(substr($this->getOutData(), $realLength));
        }

        $this->dbg(Logger::DBG_SOCK, 'SEND ' . $this->getKey() . ": $realLength bytes");

        if (!$this->getOutData()) {
            $this
                ->unsetSends()
                ->setRecvs();
        }

        return $this;
    }

    /**
     * Read data from socket
     * @param $key
     * @return bool
     */
    public function read(): bool
    {
        if (($data = stream_get_contents($this->fd)) === false) {
            $this->err('ERROR: read from stream error');
        }

        if (!$data)	{	// удаленный сервер разорвал соединение
            /*
            if ($this->sockets[$key][self::EXT_KEY]) {		// если соединение разорвал клиентский сокет, то закрываем и внешний
                $this->closeConnection($this->sockets[$key][self::EXT_KEY]);
            }
            */
            $this->close();
            return false;
        }

        $this->inData .= $data;

        $this->dbg(Logger::DBG_SOCK, 'RECV to ' . $this->getKey() . ': '. $data);

        return $this->packetParser();
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

        $this->dbg(Logger::DBG_SOCK, 'Socket ' . $this->getKey() . ' closed');
    }

    private function badData(): bool
    {
// TODO продумать действия при закрытии сокета, на который поступили плохие данные
        $this->close();
        return false;
    }

    /**
     * Is paket from client or from external connection
     * @param $key
     * @return bool
     */
    private function packetParser(): bool
    {
        $packet = $this->inData;
        $this->dbg(Logger::DBG_SOCK,'Packet: ' . $packet);
        $this->inData = '';

        if ($this->request) {
            return $this->request->addPacket($packet);
        }

        $this->requestStr .= $packet;
        $requestStrLen = strlen($this->requestStr);

// if data length less than need for get declared length - return and wait more packets
        if ($requestStrLen < Request::getLengthLen()) {
            return false;
        }

        if (!$this->requestLen) {
            $this->requestLen = Request::getLength($this->requestStr);
        }

// if real request length more than declared length - incoming data is bad
        if ($requestStrLen > $this->requestLen) {
            $this->dbg(Logger::DBG_SOCK,"BAD DATA real request length $requestStrLen more than declared length $this->requestLen: " . $this->requestStr);
            return $this->badData();
        }

        if ($requestStrLen < Request::getSpawnLen()) {
            return false;
        }

// if cannot create class of request by declared type - incoming data is bad
        $requestType = Request::getType($this->requestStr);
        if (!$this->request = Request::spawn($this, $requestType)) {
            $this->dbg(Logger::DBG_SOCK,'BAD DATA cannot create class of request by declared type: ' . $requestType);
            return $this->badData();
        }

        return $this->request->addPacket($packet);
    }
}