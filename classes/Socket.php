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

    /** @var Request  */
    private $request;
    public function setRequest($val) {$this->request = $val; return $this;}
    public function getRequest() {return $this->request;}

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
     * Is paket from client or from external connection
     * @param $key
     * @return bool
     */
    private function packetParser() {
        $packet = $this->inData;
        $this->dbg(Logger::DBG_SOCK,'Packet: ' . $packet);
        $this->inData = '';

        if (!$this->request) {
            $this->request = Request::create($this, $packet);
        } else {
            $this->request->addStr($packet);
        }

        $aliveLen = Request::FLD_LENGTH_LEN + Request::FLD_TYPE_LEN;
        $reqLen = $this->request->getLen();

        if ($reqLen < $aliveLen) {
            return false;
        } else if ($reqLen === $aliveLen) {
            $server = $this->getServer();

            if ($packet === $server->getAlive(Server::ALIVE_REQ)) {				// запрос "жив ли демон" не отдаем обработчику пакетов, сразу отвечаем клиенту "жив"
                $this->dbg(Logger::DBG_SOCK,'Alive request');
                $this->addOutData($server->getAlive(Server::ALIVE_RES));
                return false;
            }

            if ($packet === $server->getAlive(Server::ALIVE_RES)) {			// ответ "демон жив" не перенаправляем клиенту
                $this->dbg(Logger::DBG_SOCK,'Alive response');
                return true;                            // true возвращается только при получении пакета "демон жив"
            }
        }



// TODO вызов обработчика пакетов - определяем тип, необходимые действия, кому направлять пакет дальше, что отвечать клиенту
//    $answer = $this->app->parser($this->app, $this, $key, $packet);
// направляем сообщение во внешний канал (канал определяется обработчиком пакетов)
//        $this->addSending($answerExt, $this->sockets[$key][self::EXT_KEY]);
// направляем сообщение клиенту
//        $this->addSending($answerClnt, $this->sockets[$key][self::CLNT_KEY]);

        return false;
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
}