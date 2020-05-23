<?php
/**
 * Universal logging tool
 */
class Logger extends aBase
{
    public const DBG_LOCATOR  = 1;
    public const DBG_SERV =     2;
    public const DBG_SOCK =     4;
    public const DBG_MESS =     8;
    public const DBG_POOL =     16;
    public const DBG_TASK =     32;
    public const DBG_NODE =     64;
    public const DBG_ADDR =     128;
    public const DBG_DBA =      256;
    public const DBG_MSG_FLD =  512;
    public const DBG_ROW =      1024;
    public const DBG_ROW_SET =  2048;
    public const DBG_TRANS =    4096;


    private static $flags = array(
        self::DBG_LOCATOR =>    'Locator',
        self::DBG_SERV =>       'Server ',
        self::DBG_SOCK =>       'Socket ',
        self::DBG_MESS =>       'Message',
        self::DBG_POOL =>       'Pool   ',
        self::DBG_TASK =>       'Task   ',
        self::DBG_NODE =>       'Node   ',
        self::DBG_ADDR =>       'Address',
        self::DBG_DBA =>        'DBA    ',
        self::DBG_MSG_FLD =>    'Msg Fld',
        self::DBG_ROW =>        'DB Row ',
        self::DBG_ROW_SET =>    'DB RSet',
        self::DBG_TRANS =>      'Trans  ',
    );

    /** @var string */
    private $logFile;
    public function setLogFile($val) : self {$this->logFile = $val; return $this;}

    /** @var string */
    private $errFile;
    public function setErrFile($val) :  self {$this->errFile = $val; return $this;}

    /** @var string */
    private $phpErrFile;
    public function setPhpErrFile($val) : self {$this->phpErrFile = $val; return $this;}
    public function getPhpErrFile() : string {return $this->phpErrFile;}

    /** @var bool */
    private $dbgMode = 0;
    public function setDbgMode($val) : self {$this->dbgMode = $val; return $this;}

    /**
     * Creating Logger object
     *
     * @param App $locator
     * @param string $logPath
     * @param int $dbgLevels
     * @param string $logName
     * @param string $errName
     * @param string $phpErrName
     * @return self
     */
    public static function create(
        aLocator $locator,
        int $dbgLevels,
        string $logPath,
        string $logExt,
        string $logName,
        string $errName,
        string $phpErrName
    ) : self
    {
        $me = new self($locator);

        $me->setLogFile($logPath . $logName . $logExt);
        $me->setErrFile($logPath . $errName . $logExt);
        $me->setPhpErrFile($logPath . $phpErrName . $logExt);
        $me->setDbgMode($dbgLevels);

        $me->getLocator()->setLogger($me);

        ini_set('error_log', $me->getPhpErrFile());

        return $me;
    }

    /**
     * Create log record
     * @param $message
     * @param bool $isErr
     * @param int $debug
     * @return string
     */
    private function createRecord($message, $isErr  = false, $debug = 0) : string
    {
        echo substr($message, 0, 3) . "@@@";
        $record =
            $this->getDate() . "\t"
            . $this->getLocator()->getPid() . "\t"
            . $this->getLocator()->getName() . "\t";

        if ($isErr) {
            $record .= '[error]' . "\t\t";
        } else if ($debug) {
            $record .= '[dbg ' . self::$flags[$debug] . ']' . "\t";
        } else {
            $record .= "\t\t";
        }

        $record .= $message . "\n";

        return $record;
    }

    /**
     * Usual log
     * @param string $message
     */
    public function simpleLog(string $message) : void
    {
        $this->write($this->logFile, $this->createRecord($message));
    }

    /**
     * Debug logging
     * @param string $message
     */
    public function debugLog(int $dbgLevel, string $message) : void
    {
        if (!($this->dbgMode & $dbgLevel)) return;

        $this->write($this->logFile, $this->createRecord($message, false, $dbgLevel));
    }

    /**
     * Error logging
     * @param string $message
     */
    public function errorLog(string $message) : void
    {
        $this->write($this->logFile, $this->createRecord($message, true));
        $this->write($this->errFile, $this->createRecord($message, true));
    }

    /**
     * @return string
     */
    private function getDate() : string
    {
        return date('[Y-M-d H:i:s O]');
    }

    /**
     * @param string $file
     * @param string $message
     */
    private function write(string $file, string $message) : void
    {
        $fd = fopen($file, 'ab');
        flock($fd, LOCK_EX);
        fwrite($fd, $message);
        flock($fd, LOCK_UN);
        fclose($fd);
    }
}