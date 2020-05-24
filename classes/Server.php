<?php
use parallel\{Channel,Runtime,Events,Events\Event,Events\Event\Type};
/**
 * Work with sockets: listen, select, accept, read, write
 */
class Server extends aBase implements constMessageParsingResult
{
    protected static $dbgLvl = Logger::DBG_SERV;

    private const MAX_SOCK = MAX_SOCKETS;
    private const RESERVE_SOCK = RESERVE_SOCKETS;

    private const ALIVE_TIMEOUT = ALIVE_TIMEOUT;
    private const SELECT_TIMEOUT_SEC = SELECT_TIMEOUT_SEC;
    private const SELECT_TIMEOUT_USEC = SELECT_TIMEOUT_USEC;
    private const CONNECT_TIMEOUT = CONNECT_TIMEOUT;
    private const GARBAGE_TIMEOUT = GARBAGE_TIMEOUT;

    private const LISTEN_KEY = 'lst';
    private const KEY_PREFIX = 'sock_';

    /** @var Host */
    private $listenHost;
    public function setListenHost(Host $val) : self {$this->listenHost = $val; return $this;}
    public function getListenHost() : Host {return $this->listenHost;}

    /** @var ?Host */
    private $bindHost;
    public function setBindHost(?Host $val) : self {$this->bindHost = $val; return $this;}
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

    /** @var bool */
    private $needSleep = false;

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
    public static function create(App $app, Host $listenHost, ?Host $bindHost = null) : self
    {
        $me
            = new self($app);

        $me
            ->setListenHost($listenHost)
            ->setBindHost($bindHost)
            ->setQueue();

        $me->getLocator()->setServer($me);

        $me->dbg("Server created");

        return $me;
    }

    /**
     * Running server
     */
    public function run() : void
    {
        $this->dbg('Server started');

        $this->nowTime = time();
        $this->garbTime = time();

        $this->listen();

        while (true) {
            pcntl_signal_dispatch();

            $this->getQueue()->runTopPools();

            $this->selectAndPoll();

// regular garbage collecting
            if (($this->nowTime - $this->garbTime) >= self::GARBAGE_TIMEOUT) {
                $this->getLocator()->garbageCollect();
                $this->garbTime = $this->nowTime;
            }

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
                $this->dbg('Sockets cnt: ' . count($this->sockets));
                $this->dbg('Sends cnt: ' . count($this->sends));
                $this->dbg('Recvs cnt: ' . count($this->recvs));

// TODO добавить проверку завершения работы всех воркеров
                if (!count($this->recvs) && !count($this->sends)) {   // and no have active sockets - go out
                    break;
                }
            }
        }

//        $this->log("Sig SIGHUP : "  . var_export(pcntl_signal_get_handler(SIGHUP), true));
//        $this->log("Sig SIGTERM : "  . var_export(pcntl_signal_get_handler(SIGTERM), true));

        $this->hardFinish();
    }

    private function selectAndPoll() : bool
    {
        $this->needSleep  = true;
        $this->nowTime = time();

        $this->select();

        $result = $this->workerEventsPoll();

// sleep if no events from sockets and workers detected
        if ($this->needSleep) {
            sleep(self::SELECT_TIMEOUT_SEC);
            usleep(self::SELECT_TIMEOUT_USEC);
        }

        return $result;  // self::MESSAGE_PARSED (daemon is alive) or self::MESSAGE_NOT_PARSED
    }

    private function workerEventsPoll() : bool
    {
        $result = self::MESSAGE_NOT_PARSED;

        /** @var App $app */
        $app = $this->getLocator();
        $event = null;

        while ($event = $app->getEvents()->poll()) { // Returns non-null if there is an event
            $this->dbg("Event detected");
            $this->needSleep  = false;

            $threadId = $event->source;
            $app->getEvents()->addChannel($app->getChannelFromWorker($threadId));

// TODO добавить отправку и обработку служебных сообщений через канал (закрытие воркера при завершении работы, cмена ноды)
            if ($event->type === parallel\Events\Event\Type::Read) {
                $this->dbg("Read event");
                if (is_array($event->value) && count($event->value) === 2) {
                    [$legateId, $serializedLegate] = $event->value;

                    $this->dbg("Event for socket $legateId detected");

                    if (($socket = $this->getSocket($legateId)) === null) {
                        throw new Exception("Don't know about socket with key $legateId");
                    }

                    if ($socket->workerResponseHandler($serializedLegate) === self::MESSAGE_PARSED) {
                        $result = self::MESSAGE_PARSED;
                    }
                } else {
                    throw new Exception("Bad event value from worker: \n" . var_export($event->value, true) . "\n");
                }
            } else {
                throw new Exception("Bad event from worker: \n" . var_export($event, true) . "\n");
            }

            $event = null;
        }

        return $result; // self::MESSAGE_PARSED (daemon is alive) or self::MESSAGE_NOT_PARSED
    }

    /**
     * Select sockets
     * return true only if received AliveRes or BusyRes message
     * @return bool
     * @throws Exception
     */
    private function select() : void
    {
        if ($rdCnt = count($this->recvs)) $rd = $this->recvs;
        else $rd = array();

        if ($wrCnt = count($this->sends)) $wr = $this->sends;
        else $wr = array();

        $en = null;
        $rn = null;
        $wn = null;

        $fdCnt = 0;

        try {	// try нужен для подавления ошибки interrupted system call, возникающей при системном сигнале
            if ($wrCnt && $rdCnt) $fdCnt = @stream_select($rd, $wr, $en, 0);
            else if ($wrCnt) $fdCnt = @stream_select($rn, $wr, $en, 0);
            else if ($rdCnt) $fdCnt = @stream_select($rd, $wn, $en, 0);
            else {
                return;
            }
        } catch (Exception $e) {
            $this->err('ERROR: select exception ' . $e->getMessage());
            return;
        }

        if ($fdCnt === false ) {
            $e = error_get_last();
// ошибка, видимо, выдается только когда приходит сигнал завершения
            $this->err("ERROR: '" . $e['message'] . "' (line " . $e['line'] . " in '" . $e['file'] . "')");
            return;
        }

        if ($fdCnt === 0) {
            return;
        }

        $this->needSleep  = false;

// пишем исходящие (делаем это в первую очередь, чтобы внешний сервер не простаивал, пока мы читаем входящие)
        foreach($wr as $fd) {
            if ($key = array_search($fd, $this->sends, true)) {
                $this->getSocket($key)->send();
                usleep(50000);  // отладочное для побайтовой отправки пакетов
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
                        $isServerBusy = true;                   // if cannot remove unused socket - accept, read "alive req", answer "Busy!" and close socket
                    }
                }

                if (($fd = @stream_socket_accept($listenFd, -1)) === false) {
                    $this->err('ERROR: accept error');
                    $this->softFinish();
                } else {
                    $acceptedSocket = $this->newReadSocket(
                        $fd,
                        Host::create(
                            $this->getLocator(),
                            $this->getListenHost()->getTransport(),
                            stream_socket_get_name($fd,true)
                        )
                    );

                    if ($isServerBusy) {
                        $this->dbg('Server is busy!');
                        $acceptedSocket->getLegate()->setServerBusy();
                    }

                    $this->dbg('Accept connection from ' . $acceptedSocket->getHost()->getTarget());
                }
            }
        }

// читаем входящие
        foreach($rd as $fd) {
            if ($fd === $listenFd) continue;

            if ($key = array_search($fd, $this->recvs, true)) {
                $this->getSocket($key)->receive();
            }
        }
    }

    /**
     * Connecting to remote host
     * @param Host $host
     * @param string $message
     * @return Socket
     */
    public function connect(Host $host, aMessage $message = null) : ?Socket
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

        $this->dbg('Connect to ' . $host->getTarget());

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
        $socket->getLegate()->setConnected();

        if ($message) {
            $socket->sendMessage($message);
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

        $this->dbg('Server listening at ' . $this->getListenHost()->getTarget());
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
                if ($this->selectAndPoll()) {  // self::MESSAGE_PARSED means daemon is alive
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

        $this->getLocator()->getDba()->close();

        $this->log('******** Daemon ' . $this->getLocator()->getPid() . ' close all sockets & finished ********');
        $this->log('******** ');

        posix_kill($this->getLocator()->getPid(), SIGKILL);
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