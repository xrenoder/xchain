<?php
/**
 * Work with sockets: listen, select, accept, read, write
 */
class Server extends AppBase
{
    public const ALIVE_REQ = 'ping';
    public const ALIVE_RES = 'pong';

    public function getAlive($req) {
        $len = strlen($req) + Request::FLD_LENGTH_LEN;
        return pack("L", $len) . $req;
    }

    private const MAX_SOCK = MAX_SOCKETS;

    /** @var Host */
    private $listenHost;
    public function setListenHost($val) {$this->listenHost = $val; return $this;}
    public function getListenHost() {return $this->listenHost;}

    /** @var Host */
    private $bindHost;
    public function setBindHost($val) {$this->bindHost = $val; return $this;}
    public function getBindHost() {return $this->bindHost;}

    private const ALIVE_TIMEOUT = 10;
    private const SELECT_TIMEOUT_SEC = 0;
    private const SELECT_TIMEOUT_USEC = 50000;
    private const CONNECT_TIMEOUT = 30;
    private const GARBAGE_TIMEOUT = 300;

    private const LISTEN_KEY = 'lst';
    private const KEY_PREFIX = 'sock_';

    /** @var bool */
    private $end = false;

    /** @var Socket[] */
    private $sockets = array();
    public function setSocket($val, $key) {$this->sockets[$key] = $val; return $this;}
    public function unsetSocket($key) {unset($this->sockets[$key]); return $this;}
    public function getSocket($key) {return ($this->sockets[$key] ?? null);}

    private $sends = array();
    public function setSends($val, $key) {$this->sends[$key] = $val; return $this;}
    public function unsetSends($key) {unset($this->sends[$key]); return $this;}

    private $recvs = array();
    public function setRecvs($val, $key) {$this->recvs[$key] = $val; return $this;}
    public function unsetRecvs($key) {unset($this->recvs[$key]); return $this;}

    private $sockCounter = 0;


    private $nowTime;
    private $garbTime;

    /**
     * Creating Server object
     * @param App $app
     * @param Host $listenHost
     * @param Host $bindHost
     * @return Server
     */
    public static function create(App $app, Host $listenHost, Host $bindHost = null): Server
    {
        $me = new self($app);

        $me->setListenHost($listenHost);
        $me->setBindHost($bindHost);

        $me->getApp()->setServer($me);

        return $me;
    }

    /**
     * Running server
     */
    public function run(): void
    {
        $this->nowTime = time();
        $this->garbTime = time();

        $this->listen();

        while (true) {
            pcntl_signal_dispatch();
            $this->garbageCollect();

            $this->select();

// проверка истечения таймаутов и отключение просроченых сокетов
            /*
                        foreach(static::$sockets as $key => $socket) {
                            if ($socket[static::KEEP_KEY]) continue;

                            if ((static::$nowTime - $socket[static::BEG_KEY]) > static::CLIENT_TIMEOUT) {
                                static::log("Client closed by timeout");
                                static::closeConnection($key);
                            }
                        }
            */

            if ($this->end) { 	// если установлен режим "мягкого завершения работы"
                $this->dbg(Logger::DBG_SERV,'Sockets cnt: ' . count($this->sockets));

                if (!count($this->sockets)) {   // и нет подключенных сокетов - выходим
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
     * @return bool
     */
    private function select(): bool
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
            $this->err('ERROR: select error (signal interruption?)');        // ошибка выдается только когда приходит сигнал завершения
            return false;
        } else if ($fdCnt === 0) {

            /*
            if (count($this->reserve) < self::RESERVE_NEED) {					// при необходимости добиваем нужное количество внешних резервных соединений
                if ($key = $this->connect(static::$hostOut, static::$portOut)) {
                    array_push(static::$reserve, $key);
                    static::log("Reserve connection created: $key");
                }
            }
            */

            return false;
        }

// пишем исходящие (делаем это в первую очередь, чтобы внешний сервер не простаивал, пока мы читаем входящие)
        foreach($wr as $fd) {
            if ($key = array_search($fd, $this->sends)) {
                $this->getSocket($key)->write();
            }
        }

// проверяем новые подключения
        $listenFd = null;

        if ($listenSocket = $this->getSocket(self::LISTEN_KEY)) {
            $listenFd = $listenSocket->getFd();

            if (in_array($listenFd, $rd)) {
                if (($fd = @stream_socket_accept($listenFd)) === false) {
                    $this->err('ERROR: accept error');
                    $this->softFinish();
                } else {
                    $this->dbg(Logger::DBG_SERV,'Accept connection');
                    $this->newReadSocket($fd);
                }
            }
        }

// читаем входящие
        $isAlive = false;

        foreach($rd as $fd) {
            if ($fd === $listenFd) continue;

            if ($key = array_search($fd, $this->recvs)) {
                if ($this->getSocket($key)->read()) {
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
    private function connect(Host $host, string $dataSend): ?Socket
    {
        /*
                if ($host === static::$usock) {
                    $transport = static::UNIX_TRANSPORT;
                    $target = static::$usock;
                } else if ($port) {
                    $target = $host . ":" . $port;
                    if (static::$sslMode) $transport = static::SSL_TRANSPORT;
                    else $transport = static::TCP_TRANSPORT;
                } else {
                    static::err("ERROR: TCP socket need port for connection");
                }
        */

// TODO проверить, как влияет на скорость опция TCP_DELAY и другие (so_reuseport,
        $opts = array(
            'socket' => array(
                'tcp_nodelay' => true,
            ),
        );

        if ($this->bindHost) {
            $opts['socket']['bindto'] = $this->getBindHost()->getPair();
        }

        $context = stream_context_create($opts);

        /*
                if ($transport == static::SSL_TRANSPORT) {
                    stream_context_set_option($context, 'ssl', 'verify_peer', false);	// или true?
                    stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
        //			stream_context_set_option($context, 'ssl', 'verify_host', true);
        //			stream_context_set_option($context, 'ssl', 'cafile', static::$sslCert);
                    stream_context_set_option($context, 'ssl', 'local_cert', static::$sslCert);
                    stream_context_set_option($context, 'ssl', 'passphrase', static::$sslPass);
                }
        */

        $fd = null;

        $errNo = -1;
        $errStr = '';

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

        if (!$fd) return null;

        $socket = $this->newWriteSocket($fd);
        $socket->addOutData($dataSend);

        $this->dbg(Logger::DBG_SERV,'Connect to ' . $host->getTarget());

        return $socket;
    }

    /**
     * Listen socket
     */
    private function listen(): void
    {
        if ($this->getSocket(self::LISTEN_KEY)) return;

/*
        if (static::$inTransport == static::UNIX_TRANSPORT) {
            $target = static::$usock;
        } else {
//			$target = static::$ipIn . ":" . static::$portIn;
            $target = static::LOCALHOST . ":" . static::$portIn;
        }
*/
        $fd = stream_socket_server($this->getListenHost()->getTarget(), $errNo, $errStr);

        if (!$fd) {
            $this->err("ERROR: cannot create server socket ($errStr)");
        }

        $socket = $this->newReadSocket($fd, self::LISTEN_KEY);
        $this->dbg(Logger::DBG_SERV,"Server listening");
    }

    private function newReadSocket($fd, string $key = null): Socket
    {
        return $this->newSocket($fd, $key, true);
    }

    private function newWriteSocket($fd, string $key = null): Socket
    {
        return $this->newSocket($fd, $key, false);
    }

    /**
     * Create new socket, add it to reading select array ($toRead = true) or to writing select array ($toRead = false)
     * @param $fd
     * @param $key
     * @param bool $toRead
     * @return Socket
     */
    private function newSocket($fd, $key, bool $toRead ): Socket
    {
        if ($key === null) {
            $key = $this->getSocketKey();
        }

        $socket = Socket::create($this, $fd, $key);
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
     * Close listening socket if exists
     * @param $key
     */
    private function closeSocket($key): void
    {
        if (!$socket = $this->getSocket($key)) return;

        $socket->close();
        unset($socket);
    }

    /**
     * Create new key for socket
     * @return string
     */
    private function getSocketKey(): string
    {
        while(true) {
            $this->sockCounter++;
            if ($this->sockCounter === self::MAX_SOCK) $this->sockCounter = 1;

            $key = self::KEY_PREFIX . $this->sockCounter;
            if(!isset($this->sockets[$key])) break;
        }

        return $key;
    }

    /**
     * Collect garbage for optimal memory usage
     */
    private function garbageCollect(): void
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
     */
    public function isDaemonAlive(): bool
    {
        $result = false;

        if (
            $socket = $this->connect(
                $this->getListenHost(),
                $this->getAlive(Server::ALIVE_REQ)
            )
        ) {
            $beg = time();

            while ((time() - $beg) < self::ALIVE_TIMEOUT) {
                if ($result = $this->select()) {
                    break;
                }
            }

            $socket->close();
        }

        return $result;
    }

    /**
     * Finish server without waiting end of current operations
     */
    public function hardFinish(): void
    {
        $this->closeSocket(self::LISTEN_KEY);

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
    public function softFinish(): void
    {
        $this->end = true;
        $this->closeSocket(self::LISTEN_KEY);
    }
}