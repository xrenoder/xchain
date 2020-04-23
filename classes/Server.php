<?php
/**
 * Work with sockets: listen, select, accept, read, write
 */
class Server extends AppBase
{
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
    private const GARBAGE_TIMEOUT = 3600;

    private const ALIVE_REQ = 'xrenoping';
    private const ALIVE_RES = 'xrenopong';

    private const LISTEN_KEY = 'listen';
    private const FD_KEY = 'fd';
    private const BEG_KEY = 'beg';
    private const INDATA_KEY = 'in';
    private const OUTDATA_KEY = 'out';
    private const KEEP_KEY = 'keep';
    private const KEY_PREFIX = 'key';

    /** @var bool */
    private $end = false;
    private $listenSocket = null;

    /** @var Socket[] */
    private $sockets = array();
    public function setSocket($val, $key) {$this->sockets[$key] = $val; return $this;}
    public function getSocket($key) {return $this->sockets[$key];}

    private $sends = array();
    private $recvs = array();

    private $sockCounter = 0;
    private $maxSockCounter = 1024;

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

            if ($this->end && count($this->sockets) <= 1) { 	// если установлен режим "мягкого завершения работы"
                break;												// и нет подключенных клиентов - выходим
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
        $this->nowTime = time();

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
                $this->write($key);
            }
        }

// проверяем новые подключения
        if ($this->listenSocket) {
            if (in_array($this->listenSocket, $rd)) {
                if (($fd = @stream_socket_accept($this->listenSocket)) === false) {
                    $this->err('ERROR: accept error');
                    $this->softFinish();
                } else {
                    $this->dbg(Logger::DBG_SERV,'Accept connection');
                    $this->addNewSocket($fd);
                }

                $listenKey = array_search($this->listenSocket, $rd);        // убираем листен-сокет из временного массива готовых для чтения, чтобы не пытаться из него читать, там все равно пусто
                unset($rd[$listenKey]);
            }
        }

// читаем входящие
        $isAlive = false;

        foreach($rd as $fd) {
            if ($key = array_search($fd, $this->recvs)) {
                if ($this->read($key)) {
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
     * Read data from socket
     * @param $key
     * @return bool
     */
    private function read($key): bool
    {
        if (($data = stream_get_contents($this->sockets[$key][self::FD_KEY])) === false) {
            $this->err('ERROR: read from stream error');
        }

        if (!$data)	{	// удаленный сервер (клиент или внешний) разорвал соединение
            /*
            if ($this->sockets[$key][self::EXT_KEY]) {		// если соединение разорвал клиентский сокет, то закрываем и внешний
                $this->closeConnection($this->sockets[$key][self::EXT_KEY]);
            }
            */
            $this->closeConnection($key);
            return false;
        }

        $this->sockets[$key][self::INDATA_KEY] .= $data;

		$this->dbg(Logger::DBG_SERV,"RECV $key:" . $data);

        return $this->packetParser($key);
    }

    /**
     * Write data to socket
     * @param $key
     */
    private function write($key): void
    {
        $buff = $this->sockets[$key][self::OUTDATA_KEY];

        if (($realLength = fwrite($this->sockets[$key][self::FD_KEY], $buff, strlen($buff))) === false) {
            $this->err('ERROR: write to socket error');
        }

        if ($realLength) {
            $this->sockets[$key][self::OUTDATA_KEY] = substr($this->sockets[$key][self::OUTDATA_KEY], $realLength);
        }

        $this->dbg(Logger::DBG_SERV,"SEND $key: $realLength bytes");

        if (!$this->sockets[$key][self::OUTDATA_KEY]) {
            unset($this->sends[$key]);

            /*
                        if (!static::$sockets[$key][static::KEEP_KEY]) {
                            static::testMaxTime($key);

                            static::closeConnection($key);
                        } else {
            */

            $this->recvs[$key] = $this->sockets[$key][self::FD_KEY];
//			}
        }
    }

    /**
     * Connecting to remote host
     * @param Host $host
     * @return string
     */
    private function connect(Host $host): ?string
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
            $fd = @stream_socket_client($host->getTarget(), $errNo, $errStr, static::CONNECT_TIMEOUT, STREAM_CLIENT_CONNECT, $context);
        } catch (Exception $e) {
            $this->err('ERROR: stream_socket_client exception ' . $e->getMessage());
            $this->err("DETAILS: stream_socket_client ($errNo) $errStr");
        }

        if (!$fd) return null;

        $key = $this->addNewSocket($fd);

        $this->dbg(Logger::DBG_SERV,'Connected to ' . $host->getTarget());

        return $key;
    }

    /**
     * Listen socket
     */
    private function listen(): void
    {
        if ($this->listenSocket) return;

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

        $this->nonblock($fd);

        $this->listenSocket = $fd;
        $this->recvs[self::LISTEN_KEY] = $fd;

        $this->dbg(Logger::DBG_SERV,"Server listening");
    }

    /**
     * Close listening socket if exists
     */
    private function nolisten(): void
    {
        if ($this->listenSocket) {
            $this->closeSocket($this->listenSocket);
            unset($this->recvs[self::LISTEN_KEY]);
            $this->listenSocket = null;
        }
    }

    /**
     * Close socket
     * @param $fd
     */
    private function closeSocket($fd): void
    {
        stream_socket_shutdown($fd, STREAM_SHUT_RDWR);
        fclose($fd);
    }

    /**
     * Close connection
     * @param $key
     */
    private function closeConnection($key): void
    {
        if (!$key) return;

        if (isset($this->sockets[$key])) {
            $this->closeSocket($this->sockets[$key][self::FD_KEY]);
            unset($this->sockets[$key]);
        }

        if (isset($this->sends[$key])) unset($this->sends[$key]);
        if (isset($this->recvs[$key])) unset($this->recvs[$key]);

        $this->dbg(Logger::DBG_SERV,"Connection $key closed");
    }

    /**
     * Fill socket data
     * @param string $key
     * @param $fd
     * @param int $time
     * @param $clientKey
     */
    private function fillSocket(string $key, $fd): void
    {
        $this->sockets[$key][self::FD_KEY] = $fd;
//		$this->sockets[$key][self::KEEP_KEY] = true;
        $this->sockets[$key][self::BEG_KEY] = time();
        $this->sockets[$key][self::INDATA_KEY] = '';
        $this->sockets[$key][self::OUTDATA_KEY] = '';
    }

    /**
     * Set socket to nonblocking mode
     * @param $fd
     */
    private function nonblock($fd): void
    {
        if (!stream_set_blocking($fd, 0)) {
            $this->err('ERROR: cannot set nonblock mode');
        }
    }

    /**
     * Create new key for socket
     * @return string
     */
    private function getSocketKey(): string
    {
        while(true) {
            $this->sockCounter++;
            if ($this->sockCounter === $this->maxSockCounter) $this->sockCounter = 1;

            $key = self::KEY_PREFIX . $this->sockCounter;
            if(!isset($this->sockets[$key])) break;
        }

        return $key;
    }

    /**
     * Add packet to socket for sending
     * @param $packet
     * @param $key
     */
    private function addSending($packet, $key): void
    {
        if (isset($this->sockets[$key])) {
            $this->sockets[$key][self::OUTDATA_KEY] .= $packet;
            $this->sends[$key] = $this->sockets[$key][static::FD_KEY];
        }
    }

    /**
     * Add new client connection to sockets
     * @param $fd
     * @return string
     */
    private function addNewSocket($fd): string
    {
        $key = $this->getSocketKey();
        $this->nonblock($fd);
        $this->fillSocket($key, $fd);
        $this->recvs[$key] = $fd;

        return $key;
    }

    /**
     * Is paket from client or from external connection
     * @param $key
     * @return bool
     */
    private function packetParser($key) {
        $packet = $this->sockets[$key][self::INDATA_KEY];
        $this->dbg(Logger::DBG_SERV,'Packet: ' . $packet);
        $this->sockets[$key][self::INDATA_KEY] = '';

        if ($packet === self::ALIVE_REQ) {				// запрос "жив ли демон" не отдаем обработчику пакетов, сразу отвечаем клиенту "жив"
            $this->dbg(Logger::DBG_SERV,'Alive request');
            $this->addSending(self::ALIVE_RES, $key);
            return false;
        }

        if ($packet === self::ALIVE_RES) {			// ответ "демон жив" не перенаправляем клиенту
            $this->dbg(Logger::DBG_SERV,'Alive response');
            return true;                            // true возвращается только при получении пакета "демон жив"
        }

// TODO вызов обработчика пакетов - определяем тип, необходимые действия, кому направлять пакет дальше, что отвечать клиенту
//    $answer = $this->app->parser($this->app, $this, $key, $packet);

// направляем сообщение во внешний канал (канал определяется обработчиком пакетов)
//        $this->addSending($answerExt, $this->sockets[$key][self::EXT_KEY]);
// направляем сообщение клиенту
//        $this->addSending($answerClnt, $this->sockets[$key][self::CLNT_KEY]);

        return false;			// true возвращается только при получении пакета "демон жив"
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

        if ($key = $this->connect($this->getListenHost())) {
            $this->addSending(self::ALIVE_REQ, $key);

            $beg = time();

            while ((time() - $beg) < self::ALIVE_TIMEOUT) {
                if ($result = $this->select()) {
                    break;
                }
            }

            $this->closeConnection($key);
        }

        return $result;
    }

    /**
     * Finish server without waiting end of current operations
     */
    public function hardFinish(): void
    {
        $this->nolisten();

        foreach ($this->sockets as $key => $socket) {
            $this->closeSocket($this->sockets[$key][self::FD_KEY]);
        }

        $this->log(' ******** Daemon ' . $this->getApp()->getDaemon()->getPid() . ' close all sockets & finished ********');
        $this->log(' ******** ');

        exit(0);
    }

    /**
     * Finish server with waiting end of current operations
     */
    public function softFinish(): void
    {
        $this->end = true;
        $this->nolisten();
    }
}