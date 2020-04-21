<?php
/**
 * Universal logging tool
 */
class Logger extends AppBase
{
    /** @var string */
    private $logFile;
    public function setLogFile($val) {$this->logFile = $val; return $this;}
//    public function getLogFile() {return $this->logFile;}

    /** @var string */
    private $errFile;
    public function setErrFile($val) {$this->errFile = $val; return $this;}
//    public function getErrFile() {return $this->errFile;}

    /** @var string */
    private $phpErrFile;
    public function setPhpErrFile($val) {$this->phpErrFile = $val; return $this;}
    public function getPhpErrFile() {return $this->phpErrFile;}

    /**
     * Creating Logger object
     *
     * @param App $app
     * @param string $logPath
     * @param string $logName
     * @param string $errName
     * @param string $phpErrName
     * @return Logger
     */
    public static function create(App $app, string $logPath, string $logName, string $errName, string $phpErrName): Logger
    {
        $me = new self($app);

        $me->setLogFile($logPath . $logName);
        $me->setErrFile($logPath . $errName);
        $me->setPhpErrFile($logPath . $phpErrName);

        $me->getApp()->setLogger($me);

        return $me;
    }

    /**
     * @param string $message
     */
    public function simpleLog(string $message): void
    {
        $this->write($this->logFile, $this->getDate() . ' ' . $message . "\n");
    }

    /**
     * @param string $message
     */
    public function errorLog(string $message): void
    {
        $this->write($this->errFile, $this->getDate() . ' ' . $message . "\n");
    }

    /**
     * @return string
     */
    private function getDate(): string
    {
        return date('[Y-M-d H:i:s O]');
    }

    /**
     * @param string $file
     * @param string $message
     */
    private function write(string $file, string $message): void
    {
        $fd = fopen($file, 'ab');
        flock($fd, LOCK_EX);
        fwrite($fd, $message);
        flock($fd, LOCK_UN);
        fclose($fd);
    }
}