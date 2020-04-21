<?php
/**
 * Application class
 */
class App
{
    /** @var string */
    private $name = null;
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
    public function setServer($val) {$this->server = $val; return $this;}
    public function getServer() {return $this->server;}

    /**
     * AppBase constructor.
     * @param App $app
     */
    public function __construct(string $name)
    {
        $this->setName($name);
    }

    /**
     * Running application
     */
    public function run(string $command)
    {
        try {
            if (!$this->getDaemon()->run($command)) {
                throw new Exception('Cannot daemon start');
            }

            $this->getServer()->run();

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}