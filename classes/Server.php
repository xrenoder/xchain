<?php
/**
 * Work with sockets: listen, select, accept, read, write
 */
class Server extends aBase
{
    protected static $dbgLvl = Logger::DBG_SERV;

    private const MAX_SOCK = MAX_SOCKETS;
    private const RESERVE_SOCK = RESERVE_SOCKETS;

    private const ALIVE_TIMEOUT = 10;
    private const SELECT_TIMEOUT_SEC = 0;
    private const SELECT_TIMEOUT_USEC = 50000;
    private const CONNECT_TIMEOUT = 30;
    private const GARBAGE_TIMEOUT = 300;

    private const LISTEN_KEY = 'lst';
    private const KEY_PREFIX = 'sock_';

    /** @var Host */
    private $listenHost;
    public function setListenHost(Host $val) : self {$this->listenHost = $val; return $this;}
    public function getListenHost() : Host {return $this->listenHost;}

    /** @var Host */
    private $bindHost;
    public function setBindHost(Host $val) : self {$this->bindHost = $val; return $this;}
    public function getBindHost() : Host {return $this->bindHost;}

    /** @var Queue */
    private $queue = null;
    public function setQueue(): self {if (!$this->queue) $this->queue = Queue::create($this); return $this;}
    public function getQueue() : Queue {return $this->queue;}

    /** @var Socket[] */
    private $sockets = array();
    public function setSocket(Socket $val, string $key) : self {$this->sockets[$key] = $val; return $this;}
    public function unsetSocket(string $key) : self {unset($this->sockets[$key]); return $this;}
    public function getSocket(string $key) : ?Socket {return ($this->sockets[$key] ?? null);}

    /** @var Socket[] */
    private $sends = array();
    public function setSends($val, string $key) : self {$this->sends[$key] = $val; return $this;}
    public function unsetSends(string $key) : self {unset($this->sends[$key]); return $this;}

    /** @var Socket[] */
    private $recvs = array();
    public function setRecvs($val, string $key) : self {$this->recvs[$key] = $val; return $this;}
    public function unsetRecvs(string $key) : self {unset($this->recvs[$key]); return $this;}

    /* unused connected socket (not include accepted)) */
    private $freeConnected = array(); /* 'host' => array('key' => socket) */
    public function getFreeConnected(Host $host) : ?Socket {$ip = $host->getKey(); return (isset($this->freeConnected[$ip]) && !empty($this->freeConnected[$ip])) ? $this->freeConnected[$ip][0] : null;}

    /** @var int[] */
    private $freeConnectedTime = array(); /* 'key' => 'freeTime' */

    /* unused accepted socket (not include connected)) */
    private $freeAccepted = array(); /* 'host' => array('key' => socket) */
    public function getFreeAccepted(Host $host) : ?Socket {$ip = $host->getKey(); return (isset($this->freeAccepted[$ip]) && !empty($this->freeAccepted[$ip])) ? $this->freeAccepted[$ip][0] : null;}

    /** @var int[] */
    private $freeAcceptedTime = array(); /* 'key' => 'freeTime' */

    /** @var int */
    private $keyCounter = 0;

    /** @var int */
    private $nowTime;
    /** @var int */
    private $garbTime;

    /** @var bool */
    private $finishFlag = false;

    /**
     * Creating Server object
     * @param App $app
     * @param Host $listenHost
     * @param Host $bindHost
     * @return self
     */
    public static function create(App $app, Host $listenHost, Host $bindHost = null) : self
    {
        $me
            = new self($app);

        $me
            ->setListenHost($listenHost)
            ->setBindHost($bindHost)
            ->setQueue();

        $me->getApp()->setServer($me);

        return $me;
    }

    /**
     * Running server
     */
    public function run() : void
    {
        $this->nowTime = time();
        $this->garbTime = time();

        $this->listen();

        while (true) {
            pcntl_signal_dispatch();
            $this->garbageCollect();

            $this->getQueue()->runTopPools();

            $this->select();

// TODO проверка истечения таймаутов чтения-записи и отключение просроченых сокетов
            /*
                        foreach(static::$sockets as $key => $socket) {
                            if ($socket[static::KEEP_KEY]) continue;

                            if ((static::$nowTime - $socket[static::BEG_KEY]) > static::CLIENT_TIMEOUT) {
                                static::log("Client closed by timeout");
                                static::closeConnection($key);
                            }
                        }
            */

            if ($this->finishFlag) { 	// if mode 'soft finish' setted
                $this->dbg(static::$dbgLvl,'Sockets cnt: ' . count($this->sockets));
                $this->dbg(static::$dbgLvl,'Sends cnt: ' . count($this->sends));
                $this->dbg(static::$dbgLvl,'Recvs cnt: ' . count($this->recvs));

                if (!count($this->recvs) && !count($this->sends)) {   // and no have active sockets - go out
                    break;
                }
            }
        }

//        $this->log("Sig SIGHUP : "  . var_export(pcntl_signal_get_handler(SIGHUP), true));
//        $this->log("Sig SIGTERM : "  . var_export(pcntl_signal_get_handler(SIGTERM), true));

        $this->hardFinish();
    }

    /**
     * Select sockets
     * return true only if received AliveRes or BusyRes message
     * @return bool
     * @throws Exception
     */
    private function select() : bool
    {
        if ($rdCnt = count($this->recvs)) $rd = $this->recvs;
        else $rd = array();

        if ($wrCnt = count($this->sends)) $wr = $this->sends;
        else $wr = array();

        $tSec = self::SELECT_TIMEOUT_SEC;
        $tUsec = self::SELECT_TIMEOUT_USEC;

        $en = null;
        $rn = null;
        $wn = null;

        $fdCnt = 0;

        try {	// try нужен для подавления ошибки interrupted system call, возникающей при системном сигнале
            if ($wrCnt && $rdCnt) $fdCnt = @stream_select($rd, $wr, $en, $tSec, $tUsec);
            else if ($wrCnt) $fdCnt = @stream_select($rn, $wr, $en, $tSec, $tUsec);
            else if ($rdCnt) $fdCnt = @stream_select($rd, $wn, $en, $tSec, $tUsec);
            else {
                sleep($tSec);
                usleep($tUsec);
                $this->nowTime = time();
                return false;
            }
        } catch (Exception $e) {
            $this->err('ERROR: select exception ' . $e->getMessage());
        }

        $this->nowTime = time();

        if ($fdCnt === false ) {
            $e = error_get_last();
            // ошибка, видимо, выдается только когда приходит сигнал завершения
            $this->err("ERROR: '" . $e['message'] . "' (line " . $e['line'] . " in '" . $e['file'] . "')");
            return false;
        }

        if ($fdCnt === 0) {
            return false;
        }

// пишем исходящие (делаем это в первую очередь, чтобы внешний сервер не простаивал, пока мы читаем входящие)
        foreach($wr as $fd) {
            if ($key = array_search($fd, $this->sends, true)) {
                $this->getSocket($key)->send();
            }
        }

// проверяем новые подключения
        $listenFd = null;
        $isServerBusy = false;

        if ($listenSocket = $this->getSocket(self::LISTEN_KEY)) {
            $listenFd = $listenSocket->getFd();

            if (in_array($listenFd, $rd, true)) {
                if (count($this->sockets) >= (self::MAX_SOCK)) {
                    throw new Exception('Cannot accept: reach maximal sockets count');
                }

                if (count($this->sockets) >= (self::MAX_SOCK - self::RESERVE_SOCK)) {
                    if (!$this->removeUnusedConnected()) {      // try to remove unused connected socket to accept connection
                        $isServerBusy = true;                         // if cannot remove unused socket - accept, read "@"alive req", answer "Busy!" and close socket
                    }
                }

                if (($fd = @stream_socket_accept($listenFd, -1)) === false) {
                    $this->err('ERROR: accept error');
                    $this->softFinish();
                } else {
                    $acceptedSocket = $this->newReadSocket(
                        $fd,
                        Host::create(
                            $this->getApp(),
                            $this->getListenHost()->getTransport(),
                            stream_socket_get_name($fd,true)
                        )
                    );

                    if ($isServerBusy) {
                        $this->dbg(static::$dbgLvl, 'Server is busy!');
                        $acceptedSocket->setServerBusy();
                    }

                    $this->dbg(static::$dbgLvl, 'Accept connection from ' . $acceptedSocket->getHost()->getTarget());
                }
            }
        }

// читаем входящие
        $isAlive = false;

        foreach($rd as $fd) {
            if ($fd === $listenFd) continue;

            if ($key = array_search($fd, $this->recvs, true)) {
                if ($this->getSocket($key)->receive()) {
                    $isAlive = true;
                }
            }
        }

        if ($isAlive) {
            return true;    // возвращаем true, если получен ответ ALIVE_ANSWER
        }

        return false;
    }

    /**
     * Connecting to remote host
     * @param Host $host
     * @param string $dataSend
     * @return Socket
     */
    public function connect(Host $host, string $dataSend = null) : ?Socket
    {
        if (count($this->sockets) >= self::MAX_SOCK) {
            if (!$this->removeUnusedConnected()) {      // try to remove unused connected socket to accept connection
// TODO добавить обработку ситуации "не хватает сокетов, чтобы установить коннект"
// - отправить в блокчейн транзакцию о перегружености
                return null;                            // if cannot remove unused socket - not connect
            }

        }

// TODO проверить, как влияет на скорость опция TCP_DELAY и другие (so_reuseport, backlog)
        $opts = array(
            'socket' => array(
                'tcp_nodelay' => true,
            ),
        );

        if ($this->bindHost) {
            $opts['socket']['bindto'] = $this->getBindHost()->getPair();
        }

        $context = stream_context_create($opts);

        $fd = null;

        $errNo = -1;
        $errStr = '';

        $this->dbg(static::$dbgLvl,'Connect to ' . $host->getTarget());

        try {
            $fd = @stream_socket_client(
                $host->getTarget(),
                $errNo,
                $errStr,
                static::CONNECT_TIMEOUT,
                STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT,
                $context
            );
        } catch (Exception $e) {
            $this->err('ERROR: stream_socket_client exception ' . $e->getMessage());
            $this->err("DETAILS: stream_socket_client ($errNo) $errStr");
        }

        if (!$fd) {
            $this->err("ERROR: stream_socket_client ($errNo) $errStr");
            return null;
        }

        $socket = $this->newWriteSocket($fd, $host);
        $socket->setConnected();

        if ($dataSend) {
            $socket->addOutData($dataSend);
        }

        return $socket;
    }

    /**
     * Listen socket
     */
    private function listen() : void
    {
        if ($this->getSocket(self::LISTEN_KEY)) return;

        if (count($this->sockets) >= self::MAX_SOCK) {
            throw new Exception('Cannot listening: reach maximal sockets count');
        }

        $fd = stream_socket_server($this->getListenHost()->getTarget(), $errNo, $errStr);

        if (!$fd) {
            throw new Exception("ERROR: cannot create server socket ($errStr)");
        }

        $socket = $this->newReadSocket($fd, $this->getListenHost(), self::LISTEN_KEY);

        $this->dbg(static::$dbgLvl, 'Server listening at ' . $this->getListenHost()->getTarget());
    }

    private function newReadSocket($fd, Host $host, string $key = null) : Socket
    {
        return $this->newSocket($fd, $host, $key, true);
    }

    private function newWriteSocket($fd, Host $host, string $key = null) : Socket
    {
        return $this->newSocket($fd, $host, $key, false);
    }

    /**
     * Create new socket, add it to reading select array ($toRead = true) or to writing select array ($toRead = false)
     * @param $fd
     * @param Host $host
     * @param string|null $key
     * @param bool $toRead
     * @return Socket
     * @throws Exception
     */
    private function newSocket($fd, Host $host, ?string $key, bool $toRead ) : Socket
    {
        if ($key === null) {
            $key = $this->getSocketKey();
        }

        $socket = Socket::create($this, $host,$fd, $key);
        $socket
            ->setBlockMode(false);

        if ($toRead) {
            $socket->setRecvs();
        } else {
            $socket->setSends();
        }

        return $socket;
    }

    /**
     * Close socket if exists
     * @param string $key
     */
    private function closeSocket(string $key) : void
    {
        if (!$socket = $this->getSocket($key)) return;

        $socket->close();
        unset($socket);
    }

    /**
     * Create new key for socket
     * @return string
     * @throws Exception
     */
    private function getSocketKey() : string
    {
// need checking before socket creating
        if (count($this->sockets) >= self::MAX_SOCK) {
            throw new Exception('Cannot get socket key: reach maximal sockets count');
        }

        while(true) {
            $this->keyCounter++;
            if ($this->keyCounter === self::MAX_SOCK) $this->keyCounter = 1;

            $key = self::KEY_PREFIX . $this->keyCounter;
            if (!isset($this->sockets[$key])) break;
        }

        return $key;
    }

    /**
     * Collect garbage for optimal memory usage
     */
    private function garbageCollect() : void
    {
        if (($this->nowTime - $this->garbTime) < self::GARBAGE_TIMEOUT) return;

        gc_enable();
        gc_collect_cycles();
        gc_disable();

        $this->garbTime = $this->nowTime;
    }

    /**
     * Check, is Daemon alive or not
     * @return bool
     * @throws Exception
     */
    public function isDaemonAlive() : bool
    {
        AliveTask::create($this, null,  $this->getListenHost())
            ->getPool() //->setHandler("AliveTask::poolFinishHandler")
            ->toQueue();

        if ($this->getQueue()->runOnePool()) {
            $beg = time();

            while ((time() - $beg) < self::ALIVE_TIMEOUT) {
                if ($this->select()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Finish server without waiting end of current operations
     */
    public function hardFinish() : void
    {
        $this->closeSocket(self::LISTEN_KEY);       // close first for not accepting new clients

        foreach ($this->sockets as $key => $socket) {
            $this->closeSocket($key);
        }

        $this->log('******** Daemon ' . $this->getApp()->getDaemon()->getPid() . ' close all sockets & finished ********');
        $this->log('******** ');

        exit(0);
    }

    /**
     * Finish server with waiting end of current operations
     */
    public function softFinish() : void
    {
        $this->finishFlag = true;
        $this->closeSocket(self::LISTEN_KEY);
    }

    public function freeConnected(Socket $socket, Host $host, string $key) : self
    {
        $hostKey = $host->getKey();

        $this->freeConnected[$hostKey][$key] = $socket;
        $this->freeConnectedTime[$key] = $socket->getFreeTime();

        return $this;
    }

    public function busyConnected(Host $host, string $key) : self
    {
        $hostKey = $host->getKey();

        unset($this->freeConnected[$hostKey][$key]);
        unset($this->freeConnectedTime[$key]);

        return $this;
    }

    public function freeAccepted(Socket $socket, Host $host, string $key) : self
    {
        $hostKey = $host->getKey();

        $this->freeAccepted[$hostKey][$key] = $socket;
        $this->freeAcceptedTime[$key] = $socket->getFreeTime();

        return $this;
    }

    public function busyAccepted(Host $host, string $key) : self
    {
        $hostKey = $host->getKey();

        unset($this->freeAccepted[$hostKey][$key]);
        unset($this->freeAcceptedTime[$key]);

        return $this;
    }

    private function removeUnusedConnected() : bool
    {
        if (!count($this->freeConnectedTime)) {
            return false;
        }

        $this->freeConnectedTime = asort($this->freeConnectedTime);

        $key = array_key_first($this->freeConnectedTime);

        $this->getSocket($key)->close();

        return true;
    }

/*
    private function removeUnusedAccepted() : bool
    {
        if (!count($this->freeAcceptedTime)) {
            return false;
        }

        $this->freeAcceptedTime = asort($this->freeAcceptedTime);

        $key = array_key_first($this->freeAcceptedTime[0]);

        $this->getSocket($key)->close();

        return true;
    }
*/
}