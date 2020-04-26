<?php
/**
 * Application class
 */
class App extends AppBase
{
    /** @var string */
    private $name;

    public function setName($val) {$this->name = $val; return $this;}
    public function getName() {return $this->name;}

    /** @var Logger */
    private $logger;
    public function setLogger($val) {$this->logger = $val; return $this;}
    public function getLogger() {return $this->logger;}

    /** @var Daemon */
    private $daemon;
    public function setDaemon($val) {$this->daemon = $val; return $this;}
    public function getDaemon() {return $this->daemon;}

    /** @var Server */
    private $server;
    public function setServer($val): App {$this->server = $val; return $this;}
    public function getServer() {return $this->server;}

    /**
     * App constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($this);
        $this->setName($name);
    }
}
