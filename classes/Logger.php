<?php
/**
 * Universal logging tool
 */
class Logger extends aBase
{
    public const DBG_LOCATOR  =         1;              // 1
    public const DBG_DAEMON =           2;              // 2
    public const DBG_SERVER =           4;              // 3
    public const DBG_SOCKET =           8;              // 4
    public const DBG_FLD_FMT =          16384;          // 5
    public const DBG_MESSAGE =          16;             // 6
    public const DBG_POOL =             32;             // 7
    public const DBG_TASK =             64;             // 8
    public const DBG_NODE =             128;            // 9
    public const DBG_ADDRESS =          256;            // 10
    public const DBG_DBA =              512;            // 11
    public const DBG_MSG_FLD =          1024;           // 12
    public const DBG_DB_FLD =           32768;          // 13
    public const DBG_DB_FROW =          2048;           // 14
    public const DBG_DB_MROW =          65536;          // 15
    public const DBG_DB_RSET =          4096;           // 16
    public const DBG_TRANSACT =         8192;           // 17
    public const DBG_TRANS_FLD =        131072;         // 18
    public const DBG_BLOCK =            262144;         // 19
    public const DBG_TRANS_DATA_FLD =   524288;         // 20
    public const DBG_TRANS_DATA =       1048576;        // 21
    public const DBG_BLOCK_SECTION =    2097152;        // 22
    public const DBG_BLOCK_FLD =        4194304;        // 23
    public const DBG_MESSAGE_DATA =     8388608;        // 24
    public const DBG_MESSAGE_DATA_FLD = 16777216;       // 25

    private static $flags = array(
        self::DBG_LOCATOR =>        'Locator   ',
        self::DBG_DAEMON =>         'Daemon    ',
        self::DBG_SERVER =>         'Server    ',
        self::DBG_SOCKET =>         'Socket    ',
        self::DBG_FLD_FMT =>        'Fld Format',
        self::DBG_MESSAGE =>        'Message   ',
        self::DBG_POOL =>           'Pool      ',
        self::DBG_TASK =>           'Task      ',
        self::DBG_NODE =>           'Node      ',
        self::DBG_ADDRESS =>        'Address   ',
        self::DBG_DBA =>            'DBA       ',
        self::DBG_MSG_FLD =>        'Msg Field ',
        self::DBG_DB_FLD =>         'DB Field  ',
        self::DBG_DB_FROW =>        'DB fixRow ',
        self::DBG_DB_MROW =>        'DB dynaRow',
        self::DBG_DB_RSET =>        'DB recSet ',
        self::DBG_TRANSACT =>       'Transact  ',
        self::DBG_TRANS_FLD =>      'Trans Fld ',
        self::DBG_BLOCK =>          'Block     ',
        self::DBG_TRANS_DATA_FLD => 'Trn Dt Fld',
        self::DBG_TRANS_DATA =>     'Trans Data',
        self::DBG_BLOCK_SECTION =>  'Block Sect',
        self::DBG_BLOCK_FLD =>      'Block Fld ',
        self::DBG_MESSAGE_DATA =>   'Mess Data ',
        self::DBG_MESSAGE_DATA_FLD=>'Msg Dt Fld',
    );

    /** @var string */
    private $logFile;
    public function setLogFile(string $val) : self {$this->logFile = $val; return $this;}

    /** @var string */
    private $errFile;
    public function setErrFile(string $val) :  self {$this->errFile = $val; return $this;}

    /** @var string */
    private $phpErrFile;
    public function setPhpErrFile(string $val) : self {$this->phpErrFile = $val; return $this;}
    public function getPhpErrFile() : string {return $this->phpErrFile;}

    /** @var int */
    private $dbgMode = 0;
    public function setDbgMode(int $val) : self {$this->dbgMode = $val; return $this;}
    public function getDbgMode() : int {return $this->dbgMode;}

    /** @var float */
    private $dbgTime = 0;
    public function setDbgTime(float $val) : self {$this->dbgTime = $val; return $this;}

    /** @var float */
    private $dbgTimeDiff = 0;

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

        $me
            ->setLogFile($logPath . $logName . $logExt)
            ->setErrFile($logPath . $errName . $logExt)
            ->setPhpErrFile($logPath . $phpErrName . $logExt)
            ->setDbgMode($dbgLevels)
            ->setDbgTime(microtime(true))
            ->getLocator()->setLogger($me);

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
    private function &createRecord(string &$message, int $dbgLevel, bool $isError  = false, bool $isDebug  = false) : string
    {
        $record =
            $this->getDate() . "\t"
            . $this->getLocator()->getPid() . "\t"
            . $this->getLocator()->getName() . "\t";

        if ($isError) {
            $record .= "\t" . '[err ' . self::$flags[$dbgLevel] . ']' . "\t";
        } else if ($isDebug) {
            $diff = ((float) ((int) ($this->dbgTimeDiff * 100000000))) / 100000000;
            $formatDiff = number_format($diff, 8, ".", " ");

            $record .= $formatDiff . "\t" . '[dbg ' . self::$flags[$dbgLevel] . ']' . "\t";
        } else {
            $record .= "\t\t" . self::$flags[$dbgLevel] . "\t\t";
        }

        $record .= $message . "\n";

        return $record;
    }

    /**
     * Usual log
     * @param string $message
     */
    public function simpleLog(int $dbgLevel, string &$message) : void
    {
        $this->write($this->logFile, $this->createRecord($message, $dbgLevel));
    }

    /**
     * Debug logging
     * @param string $message
     */
    public function debugLog(int $dbgLevel, string &$message) : void
    {
        if (!($this->dbgMode & $dbgLevel)) return;

        $this->dbgTimeDiff = microtime(true) - $this->dbgTime;

        $this->write($this->logFile, $this->createRecord($message, $dbgLevel, false, true));

        $this->dbgTime = microtime(true);
    }

    /**
     * Error logging
     * @param string $message
     */
    public function errorLog(int $dbgLevel, string &$message) : void
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
    private function write(string $file, string &$message) : void
    {
        $fd = fopen($file, 'ab');
        flock($fd, LOCK_EX);
        fwrite($fd, $message);
        flock($fd, LOCK_UN);
        fclose($fd);
    }
}