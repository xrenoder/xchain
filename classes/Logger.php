<?php
/**
 * Universal logging tool
 */
class Logger extends aBase
{
    public const DBG_LOCATOR  = 1;
    public const DBG_DAEMON =   2;
    public const DBG_SERVER =   4;
    public const DBG_SOCKET =   8;
    public const DBG_FLD_FMT =  16384;
    public const DBG_MESSAGE =  16;
    public const DBG_POOL =     32;
    public const DBG_TASK =     64;
    public const DBG_NODE =     128;
    public const DBG_ADDRESS =  256;
    public const DBG_DBA =      512;
    public const DBG_MSG_FLD =  1024;
    public const DBG_DB_FLD =   32768;
    public const DBG_DB_FROW =  2048;
    public const DBG_DB_MROW =  65536;
    public const DBG_DB_RSET =  4096;
    public const DBG_TRANSACT = 8192;
    public const DBG_TRANS_FLD = 131072;
    public const DBG_BLOCK =    262144;


    private static $flags = array(
        self::DBG_LOCATOR =>    'Locator  ',
        self::DBG_DAEMON =>     'Daemon   ',
        self::DBG_SERVER =>     'Server   ',
        self::DBG_SOCKET =>     'Socket   ',
        self::DBG_FLD_FMT =>    'Field Fmt',
        self::DBG_MESSAGE =>    'Message  ',
        self::DBG_POOL =>       'Pool     ',
        self::DBG_TASK =>       'Task     ',
        self::DBG_NODE =>       'Node     ',
        self::DBG_ADDRESS =>    'Address  ',
        self::DBG_DBA =>        'DBA      ',
        self::DBG_MSG_FLD =>    'Msg Field',
        self::DBG_DB_FLD =>     'DB Field ',
        self::DBG_DB_FROW =>    'DB fixRow',
        self::DBG_DB_MROW =>    'DB dynRow',
        self::DBG_DB_RSET =>    'DB recSet',
        self::DBG_TRANSACT =>   'Transact ',
        self::DBG_TRANS_FLD =>  'Trans Fld',
        self::DBG_BLOCK =>      'Block ',
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

    /** @var int */
    private $dbgMode = 0;
    public function setDbgMode($val) : self {$this->dbgMode = $val; return $this;}
    public function getDbgMode() : int {return $this->dbgMode;}

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
     * @param bool $isError
     * @param int $dbgLevel
     * @return string
     */
    private function createRecord(string $message, int $dbgLevel, bool $isError  = false, bool $isDebug  = false) : string
    {
        $record =
            $this->getDate() . "\t"
            . $this->getLocator()->getPid() . "\t"
            . $this->getLocator()->getName() . "\t";

        if ($isError) {
            $record .= '[err ' . self::$flags[$dbgLevel] . ']' . "\t";
        } else if ($isDebug) {
            $record .= '[dbg ' . self::$flags[$dbgLevel] . ']' . "\t";
        } else {
            $record .= self::$flags[$dbgLevel] . "\t";
        }

        $record .= $message . "\n";

        return $record;
    }

    /**
     * Usual log
     * @param string $message
     */
    public function simpleLog(int $dbgLevel, string $message) : void
    {
        $this->write($this->logFile, $this->createRecord($message, $dbgLevel));
    }

    /**
     * Debug logging
     * @param string $message
     */
    public function debugLog(int $dbgLevel, string $message) : void
    {
        if (!($this->dbgMode & $dbgLevel)) return;

        $this->write($this->logFile, $this->createRecord($message, $dbgLevel, false, true));
    }

    /**
     * Error logging
     * @param string $message
     */
    public function errorLog(int $dbgLevel, string $message) : void
    {
        $logStr = $this->createRecord($message, $dbgLevel, true);

        $this->write($this->logFile, $logStr);
        $this->write($this->errFile, $logStr);
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