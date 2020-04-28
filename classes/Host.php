<?php
/**
 * Host
 */

class Host extends aBaseApp
{
// https://www.php.net/manual/ru/transports.php
    public const TRANSPORT_TCP = 'tcp';
    public const TRANSPORT_UDP = 'udp';
    public const TRANSPORT_SSL = 'ssl';
    public const TRANSPORT_TLS = 'tls';
    public const TRANSPORT_UNIX = 'unix';
    public const TRANSPORT_UDG = 'udg';

    private static $transports = array(
        self::TRANSPORT_TCP,
        self::TRANSPORT_SSL,
        self::TRANSPORT_UNIX,
        self::TRANSPORT_UDP,
        self::TRANSPORT_TLS,
        self::TRANSPORT_UDG,
    );

    /** @var string */
    private $transport;
    public function setTransport($val) : self {$this->transport = $val; return $this;}
    public function getTransport() : string {return $this->transport;}

    /** @var string */
    private $host;
    public function setHost($val) : self {$this->host = $val; return $this;}
    public function getHost() : string {return $this->host;}

    /** @var int */
    private $port;
    public function setPort($val) : self {$this->port = $val; return $this;}

    /**
     * Pair is construction like 'host:port'
     * @var string
     */
    private $pair;
    public function setPair($val) : self {$this->pair = $val; return $this;}
    public function getPair() : string  {return $this->pair;}

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
     * @param string $pair
     * @return Host
     * @throws Exception
     */
    public static function create(App $app, string $transport, string $pair): Host
    {
        if (!in_array($transport, self::$transports)) {
            throw new Exception("Host class: Bad transport $transport");
        }

        [$host, $port] = explode(':', trim($pair));

        $me = new self($app);

        $me->setTransport(trim($transport));
        $me->setHost(trim($host));
        $me->setPort(trim($port));

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