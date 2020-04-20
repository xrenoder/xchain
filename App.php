<?php
/**
 * Application class
 */
class App
{
    /** @var Log */
    public $logger = null;

    /** @var Daemon */
    public $daemon = null;

    /** @var Server */
    public $server = null;

    /** @var string */
    private $name = null;

    /**
     * Running application
     */
    public function run()
    {
        $this->name = SCRIPT_NAME;
        $this->logger = new Log($this,LOG_PATH . 'xchain.log', LOG_PATH . 'error.log');

        try {
            $this->server = new Server($this,MY_IP, MY_PORT);

            $this->daemon = new Daemon($this,LOG_PATH, RUN_PATH);

            $command = null;

            if ($_SERVER['argc'] >= 2) {
                $command = $_SERVER['argv'][1];
            }

            if (!$this->daemon->start($command)) {
                throw new Exception('Cannot daemon start');
            }

            $this->server->run();
        } catch (Exception $e) {

        }
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }
}