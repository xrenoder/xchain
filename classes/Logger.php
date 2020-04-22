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

    /** @var bool */
    private $dbgMode = true;
    public function setDbgMode($val) {$this->dbgMode = $val; return $this;}
    public function getDbgMode() {return $this->dbgMode;}

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
     * Usual log
     * @param string $message
     */
    public function simpleLog(string $message): void
    {
        $this->write($this->logFile, $this->createRecord($message));
    }

    /**
     * Debug logging
     * @param string $message
     */
    public function debugLog(string $message): void
    {
        if (!$this->dbgMode) return;

        $this->write($this->logFile, $this->createRecord($message, false, true));
    }

    /**
     * Error logging
     * @param string $message
     */
    public function errorLog(string $message): void
    {
        $this->write($this->errFile, $this->createRecord($message, true));
    }

    /**
     * Create log record
     * @param $message
     * @param bool $isErr
     * @return string
     */
    private function createRecord($message, $isErr  = false, $isDebug = false): string
    {
        $record = $this->getDate() . "\t" . $this->getApp()->getDaemon()->getPid() . "\t";

        if ($isErr) {
            $record .= '[error]' . "\t";
        } else if ($isDebug) {
            $record .= '[debug]' . "\t";
        } else {
            $record .= "\t\t";
        }

        $record .= $message . "\n";

        return $record;
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