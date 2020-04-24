<?php
/**
 * Host
 */

class Host extends AppBase
{
// https://www.php.net/manual/ru/transports.php
    public const TCP = 'tcp';
    public const UDP = 'udp';
    public const SSL = 'ssl';
    public const TLS = 'tls';

    public const UNIX = 'unix';
    public const UDG = 'udg';

    private static $transports = array(
        self::TCP,
        self::SSL,
        self::UNIX,
        self::UDP,
    );

    /** @var string */
    private $transport;
    public function setTransport($val) {$this->transport = $val; return $this;}
//    public function getTransport() {return $this->transport;}

    /** @var string */
    private $host;
    public function setHost($val) {$this->host = $val; return $this;}
//    public function getHost() {return $this->host;}

    /** @var int */
    private $port;
    public function setPort($val) {$this->port = $val; return $this;}
//    public function getPort() {return $this->port;}

    /**
     * Pair is construction like 'host:port'
     * @var string
     */
    private $pair;
    public function setPair($val) {$this->pair = $val; return $this;}
    public function getPair() {return $this->pair;}

    /**
     * Target is construction like 'transport://host:port' or 'transport://host' if port not used (Unix-socket)
     * @var string
     */
    private $target;
    public function setTarget($val) {$this->target = $val; return $this;}
    public function getTarget() {return $this->target;}

    /**
     * @param App $app
     * @param string $transport
     * @param string $host
     * @param int|null $port
     * @return Host
     * @throws Exception
     */
    public static function create(App $app, string $transport, string $host, int $port = null): Host
    {
        if (!in_array($transport, self::$transports)) {
            throw new Exception("Host class: Bad transport $transport");
        }

        $me = new self($app);

        $me->setTransport($transport);
        $me->setHost($host);
        $me->setPort($port);

        if ($port !== null) {
            $me->setTarget($transport . '://' . $host . ':' . $port);
            $me->setPair($host . ':' . $port);
        } else {
            $me->setTarget($transport . '://' . $host);
            $me->setPair($host);
        }

        return $me;
    }
}