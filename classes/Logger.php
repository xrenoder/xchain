<?php
/**
 * Universal logging tool
 */
class Logger extends AppBase
{
    /** @var string */
    private $logFile = null;

    /** @var string */
    private $errFile = null;

    /**
     * Log constructor.
     * @param App $app
     * @param string $logFile
     * @param string $errFile
     */
    public function __construct(App $app, string $logFile, string $errFile)
    {
        parent::__construct($app);
        $this->logFile = $logFile;
        $this->errFile = $errFile;
    }

    public function log(string $message)
    {
        $this->write($this->logFile, $this->getDate() . ' ' . $message . "\n");
    }

    public function error(string $message)
    {
        $this->write($this->errFile, $this->getDate() . ' ' . $message . "\n");
    }

    private function getDate()
    {
        return date("[Y-M-d H:i:s O]");
    }

    private function write($file, $message)
    {
        $fd = fopen($file, "ab");
        flock($fd, LOCK_EX);
        fwrite($fd, $message);
        flock($fd, LOCK_UN);
        fclose($fd);
    }
}