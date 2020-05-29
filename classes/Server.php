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

    private const LISTEN_SOCKET_ID = 'lst';
    private const SOCKET_ID_PREFIX = 'sock_';

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
    public function setSocket(Socket $val, string $id) : self {$this->sockets[$id] = $val; return $this;}
    public function unsetSocket(string $id) : self {unset($this->sockets[$id]); return $this;}
    public function getSocket(string $id) : ?Socket {return ($this->sockets[$id] ?? null);}

    /** @var Socket[] */
    private $sends = array();
    public function setSends($val, string $id) : self {$this->sends[$id] = $val; return $this;}
    public function unsetSends(string $id) : self {unset($this->sends[$id]); return $this;}

    /** @var Socket[] */
    private $recvs = array();
    public function setRecvs($val, string $id) : self {$this->recvs[$id] = $val; return $this;}
    public function unsetRecvs(string $id) : self {unset($this->recvs[$id]); return $this;}

    /* unused connected socket (not include accepted)) */
    private $freeConnected = array(); /* 'host' => array('id' => socket) */
    public function getFreeConnected(Host $host) : ?Socket {$ip = $host->getId(); return (isset($this->freeConnected[$ip]) && !empty($this->freeConnected[$ip])) ? $this->freeConnected[$ip][0] : null;}

    /** @var int[] */
    private $freeConnectedTime = array(); /* 'id' => 'freeTime' */

    /* unused accepted socket (not include connected)) */
    private $freeAccepted = array(); /* 'host' => array('id' => socket) */
    public function getFreeAccepted(Host $host) : ?Socket {$ip = $host->getId(); return (isset($this->freeAccepted[$ip]) && !empty($this->freeAccepted[$ip])) ? $this->freeAccepted[$ip][0] : null;}

    /** @var int */
    private $nowTime;
    public function getNowTime() : int {return $this->nowTime;}

    /** @var int[] */
    private $freeAcceptedTime = array(); /* 'id' => 'freeTime' */

    /** @var bool */
    private $needSleep = false;

    /** @var int */
    private $idCounter = 0;

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
//            $this->dbg("Event detected");
            $this->needSleep  = false;

            $threadId = $event->source;
            $app->getEvents()->addChannel($app->getChannelFromWorker($threadId));

// TODO добавить отправку и обработку служебных сообщений через канал (закрытие воркера при завершении работы, cмена ноды)
            if ($event->type === parallel\Events\Event\Type::Read) {
//                $this->dbg("Read event");
                if (is_array($event->value) && count($event->value) === 2) {
                    [$socketId, $serializedLegate] = $event->value;

                    $this->dbg("Event for socket $socketId detected from worker $threadId");

                    if (($socket = $this->getSocket($socketId)) === null) {
                        $this->err("EXCEPTION: " . "Don't know about socket $socketId");
                        throw new Exception("Don't know about socket $socketId");
                    }

                    if ($socket->workerResponseHandler($serializedLegate) === self::MESSAGE_PARSED) {
                        $result = self::MESSAGE_PARSED;
                    }
                } else {
                    $this->err("EXCEPTION: " . "Bad event value from worker: \n" . var_export($event->value, true) . "\n");
                    throw new Exception("Bad event value from worker: \n" . var_export($event->value, true) . "\n");
                }
            } else {
                $this->err("EXCEPTION: " . "Bad event from worker: \n" . var_export($event, true) . "\n");
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
            if ($socketId = array_search($fd, $this->sends, true)) {
                $this->getSocket($socketId)->send();

                if (DBG_ONEBYTE_SEND_USLEEP) {
                    usleep(DBG_ONEBYTE_SEND_USLEEP);  // отладочное для побайтовой отправки пакетов
                }

            }
        }

// проверяем новые подключения
        $listenFd = null;
        $isServerBusy = false;

        if ($listenSocket = $this->getSocket(self::LISTEN_SOCKET_ID)) {
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

            if ($socketId = array_search($fd, $this->recvs, true)) {
                $this->getSocket($socketId)->receive();
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
        if ($this->getSocket(self::LISTEN_SOCKET_ID)) return;

        if (count($this->sockets) >= self::MAX_SOCK) {
            throw new Exception('Cannot listening: reach maximal sockets count');
        }

        $fd = stream_socket_server($this->getListenHost()->getTarget(), $errNo, $errStr);

        if (!$fd) {
            throw new Exception("ERROR: cannot create server socket ($errStr)");
        }

        $socket = $this->newReadSocket($fd, $this->getListenHost(), self::LISTEN_SOCKET_ID);

        $this->dbg('Server listening at ' . $this->getListenHost()->getTarget());
    }

    private function newReadSocket($fd, Host $host, string $id = null) : Socket
    {
        return $this->newSocket($fd, $host, $id, true);
    }

    private function newWriteSocket($fd, Host $host, string $id = null) : Socket
    {
        return $this->newSocket($fd, $host, $id, false);
    }

    /**
     * Create new socket, add it to reading select array ($toRead = true) or to writing select array ($toRead = false)
     * @param $fd
     * @param Host $host
     * @param string|null $id
     * @param bool $toRead
     * @return Socket
     * @throws Exception
     */
    private function newSocket($fd, Host $host, ?string $id, bool $toRead ) : Socket
    {
        if ($id === null) {
            $id = $this->getSocketId();
        }

        $socket = Socket::create($this, $host,$fd, $id);
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
     * @param string $id
     */
    private function closeSocket(string $id) : void
    {
        if (!$socket = $this->getSocket($id)) return;

        $socket->close();
        unset($socket);
    }

    /**
     * Create new id for socket
     * @return string
     * @throws Exception
     */
    private function getSocketId() : string
    {
// need checking before socket creating
        if (count($this->sockets) >= self::MAX_SOCK) {
            throw new Exception('Cannot get socket id: reach maximal sockets count');
        }

        while(true) {
            $this->idCounter++;
            if ($this->idCounter === self::MAX_SOCK) $this->idCounter = 1;

            $id = self::SOCKET_ID_PREFIX . $this->idCounter;
            if (!isset($this->sockets[$id])) break;
        }

        return $id;
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

            foreach ($this->sockets as $id => $socket) {
                $this->getSocket($id)->close();
            }
        }

        return false;
    }

    /**
     * Finish server without waiting end of current operations
     */
    public function hardFinish() : void
    {
        $this->closeSocket(self::LISTEN_SOCKET_ID);       // close first for not accepting new clients

        foreach ($this->sockets as $id => $socket) {
            $this->closeSocket($id);
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
        $this->closeSocket(self::LISTEN_SOCKET_ID);
    }

    public function freeConnected(Socket $socket, Host $host, string $id) : self
    {
        $hostId = $host->getId();

        $this->freeConnected[$hostId][$id] = $socket;
        $this->freeConnectedTime[$id] = $socket->getFreeTime();

        return $this;
    }

    public function busyConnected(Host $host, string $id) : self
    {
        $hostId = $host->getId();

        unset($this->freeConnected[$hostId][$id]);
        unset($this->freeConnectedTime[$id]);

        return $this;
    }

    public function freeAccepted(Socket $socket, Host $host, string $id) : self
    {
        $hostId = $host->getId();

        $this->freeAccepted[$hostId][$id] = $socket;
        $this->freeAcceptedTime[$id] = $socket->getFreeTime();

        return $this;
    }

    public function busyAccepted(Host $host, string $id) : self
    {
        $hostId = $host->getId();

        unset($this->freeAccepted[$hostId][$id]);
        unset($this->freeAcceptedTime[$id]);

        return $this;
    }

    private function removeUnusedConnected() : bool
    {
        if (!count($this->freeConnectedTime)) {
            return false;
        }

        $this->freeConnectedTime = asort($this->freeConnectedTime);

        $id = array_key_first($this->freeConnectedTime);

        $this->getSocket($id)->close();

        return true;
    }

/*
    private function removeUnusedAccepted() : bool
    {
        if (!count($this->freeAcceptedTime)) {
            return false;
        }

        $this->freeAcceptedTime = asort($this->freeAcceptedTime);

        $id = array_key_first($this->freeAcceptedTime[0]);

        $this->getSocket($id)->close();

        return true;
    }
*/
}