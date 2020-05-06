<?php
/**
 * DBA operations
 */
class DBA extends aBase
{
    // TODO сделать обработку ошибок
    private const TRANS_FD = 'fd';
    private const TRANS_OP = 'op';
    private const TRANS_KEY = 'key';
    private const TRANS_VAL = 'val';

    protected static $dbgLvl = Logger::DBG_DBA;

    protected static $dbaMode = "c-t";

    /** @var string  */
    private $dbaHandler = null;
    public function setDbaHandler(string $val) : self {$this->dbaHandler = $val; return $this;}

    /** @var string  */
    private $dbPath = null;
    public function setDbPath(string $val) : self {$this->dbPath = $val; return $this;}

    /** @var string  */
    private $dbExt = null;
    public function setDbExt(string $val) : self {$this->dbExt = $val; return $this;}

    /** @var string  */
    private $lockExt = null;
    public function setLockExt(string $val) : self {$this->lockExt = $val; return $this;}

    /** @var string  */
    private $lockFile = null;
    public function setLockFile(string $val) : self {$this->lockFile = $val; return $this;}

    private $fdDbLock = null;

    /** @var bool  */
    private $inTransaction = false;

    private $transactionStack = array();

    private $dbhTables = array();

    /**
     * @param App $app
     * @param string $handler
     * @param string $dbExt
     * @param string $lockExt
     * @return self
     */
    public static function create(App $app, string $handler, string $dbPath, string $dbExt, string $lockFile, string $lockExt) : self
    {
        $me = new static($app);

        $me
            ->setDbaHandler($handler)
            ->setDbPath($dbPath)
            ->setDbExt($dbExt)
            ->setLockExt($lockExt)
            ->setLockFile($lockFile)

            ->getApp()->setDba($me);

        return $me;
    }

    public function transactionBegin()
    {
        if ($this->inTransaction) return;

        $this->lockEx();
        $this->inTransaction = true;
    }

    public function transactionCommit()
    {
        if (!$this->inTransaction) return;

        foreach($this->transactionStack as $record) {
            $table = $record[self::TRANS_FD];
            $operation = $record[self::TRANS_OP];
            $key = $record[self::TRANS_KEY];
            $val = $record[self::TRANS_VAL];

            $this->$operation($table, $key, $val);
        }

        $this->unlock();

        $this->transactionStack = array();
        $this->inTransaction = false;
    }

    public function transactionRollback()
    {
        if (!$this->inTransaction) return;

        $this->transactionStack = array();

        $this->unlock();
        $this->inTransaction = false;
    }

    public function first(string $table) : string
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        if (!$this->inTransaction) {
            $isLock = true;
            $this->lockSh();
        } else {
            $isLock = false;
        }

        $key = dba_firstkey($this->dbhTables[$table]);

        if ($isLock) {
            $this->unlock();
        }

        return $key;
    }

    public function next(string $table) : string
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        if (!$this->inTransaction) {
            $isLock = true;
            $this->lockSh();
        } else {
            $isLock = false;
        }

        $key = dba_nextkey($this->dbhTables[$table]);

        if ($isLock) {
            $this->unlock();
        }

        return $key;
    }

    public function check(string $table, string $key) : bool
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        if (!$this->inTransaction) {
            $isLock = true;
            $this->lockSh();
        } else {
            $isLock = false;
        }

        $result = dba_exists($key, $this->dbhTables[$table]);

        if ($isLock) {
            $this->unlock();
        }

        return $result;
    }

    public function fetch(string $table, string $key) : string
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        if (!$this->inTransaction) {
            $isLock = true;
            $this->lockSh();
        } else {
            $isLock = false;
        }

        $result = dba_fetch($key, $this->dbhTables[$table]);

        if ($isLock) {
            $this->unlock();
        }

        return $result;
    }

    public function insert(string $table, string $key, string $val)
    {
        $this->writing('realInsert', $table, $key, $val);
    }

    public function update(string $table, string $key, string $val)
    {
        $this->writing('realUpdate', $table, $key, $val);
    }

    public function close()
    {
        $this->unlock();
        $this->lockFileClose();

        foreach($this->dbhTables as $dbh) {
            dba_close($dbh);
        }
    }

    private function writing(string $operation, string $table, string $key, string $val)
    {
        if (!$this->inTransaction) {
            $this->lockEx();
            $result = $this->$operation($table, $key, $val);
            $this->unlock();
        } else {
            $record = array();

            $record[self::TRANS_FD] = $table;
            $record[self::TRANS_OP] = $operation;
            $record[self::TRANS_KEY] = $key;
            $record[self::TRANS_VAL] = $val;

            $this->transactionStack[] = $record;

            $result = true;
        }

        return $result;
    }

    private function realInsert(string $table, string $key, string $val)
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        if (!dba_insert($key, $val, $this->dbhTables[$table])) {
            throw new RuntimeException("Cannot insert record into " . $table);
        }
    }

    public function realUpdate(string $table, string $key, string $val)
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        if (!dba_replace($key, $val, $this->dbhTables[$table])) {
            throw new RuntimeException("Cannot replace record in " . $table);
        }
    }

    private function tableOpen(string $table)
    {
        $tableFile = $this->dbPath . $table . $this->dbExt;

        if(!$this->dbhTables[$table] = dba_open($tableFile, static::$dbaMode, $this->dbaHandler)) {
            throw new RuntimeException("Cannot open table file " . $tableFile);
        }
    }

    private function lockEx(bool $checkOnly = false) : bool
    {
        if (!$this->fdDbLock) $this->lockFileOpen();

        if ($checkOnly) {
            $flag = LOCK_EX | LOCK_NB;
        }  else {
            $flag = LOCK_EX;
        }

        return flock($this->fdDbLock, $flag);
    }

    private function lockSh(bool $checkOnly = false) : bool
    {
        if (!$this->fdDbLock) $this->lockFileOpen();

        if ($checkOnly) {
            $flag = LOCK_SH | LOCK_NB;
        }  else {
            $flag = LOCK_SH;
        }

        return flock($this->fdDbLock, $flag);
    }

    private function unlock() : bool
    {
        if (!$this->fdDbLock) $this->lockFileOpen();

        return flock($this->fdDbLock, LOCK_UN);
    }

    private function lockFileOpen() : bool
    {
        if ($this->fdDbLock) return true;

        $lockFile = $this->dbPath . $this->lockFile . $this->lockExt;

        $this->fdDbLock = fopen($lockFile, 'wb');

        if (!$this->fdDbLock) {
            throw new RuntimeException("Cannot open lock file " . $this->lockFile);
        }

        return true;
    }

    private function lockFileClose() : bool
    {
        if (!$this->fdDbLock) return true;
        return fclose($this->fdDbLock);
    }
}