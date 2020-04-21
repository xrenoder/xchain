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

    public function simpleLog(string $message)
    {
        $this->write($this->logFile, $this->getDate() . ' ' . $message . "\n");
    }

    public function errorLog(string $message)
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