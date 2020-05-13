<?php
/**
 * DBA operations
 */
class DBA extends aBase
{
    // TODO сделать обработку ошибок
    private const TRANS_FD = 'fd';
    private const TRANS_OP = 'op';
    private const TRANS_ID = 'id';
    private const TRANS_VAL = 'val';

    private const TRANS_KEY = 'tkey';

    protected static $dbgLvl = Logger::DBG_DBA;

    protected static $dbaMode = "cd";

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

    private $transactions = array();
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

    public function inTransaction() : bool
    {
        if (!count($this->transactions)) {
            return false;
        }

        return true;
    }

    public function transactionBegin() : string
    {
        if (!$this->inTransaction()) {
            $this->lockEx();
        }

        $transactionKey = self::TRANS_KEY . count($this->transactions);
        $this->transactions[$transactionKey] = true;

        return $transactionKey;
    }

    public function transactionCommit(string $transactionKey)
    {
        if (isset($this->transactions[$transactionKey])) {
            unset($this->transactions[$transactionKey]);
        }

        if (!count($this->transactions)) {
            foreach($this->transactionStack as $record) {
                $table = $record[self::TRANS_FD];
                $operation = $record[self::TRANS_OP];
                $id = $record[self::TRANS_ID];
                $val = $record[self::TRANS_VAL];

                $this->$operation($table, $id, $val);
            }

            $this->unlock();

            $this->transactionStack = array();
        }
    }

    public function first(string $table) : string
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        if (!$this->inTransaction()) {
            $isLock = true;
            $this->lockSh();
        } else {
            $isLock = false;
        }

        $id = dba_firstkey($this->dbhTables[$table]);

        if ($isLock) {
            $this->unlock();
        }

        return $id;
    }

    public function next(string $table) : string
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        if (!$this->inTransaction()) {
            $isLock = true;
            $this->lockSh();
        } else {
            $isLock = false;
        }

        $id = dba_nextkey($this->dbhTables[$table]);

        if ($isLock) {
            $this->unlock();
        }

        return $id;
    }

    public function check(string $table, string $id) : bool
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        $result = null;

        if (!$this->inTransaction()) {
            $isLock = true;
            $this->lockSh();
        } else {
            $isLock = false;
            $cacheId = $table . $id;

            if (isset($this->transactionStack[$cacheId])) {
                $result = true;
            }
        }

        if ($result === null) {
            $result = dba_exists($id, $this->dbhTables[$table]);
        }

        if ($isLock) {
            $this->unlock();
        }

        return $result;
    }

    public function fetch(string $table, string $id) : string
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        $result = null;

        if (!$this->inTransaction()) {
            $isLock = true;
            $this->lockSh();
        } else {
            $isLock = false;
            $cacheId = $table . $id;

            if (isset($this->transactionStack[$cacheId])) {
                $result = $this->transactionStack[$cacheId][self::TRANS_VAL];
            }
        }

        if ($result === null) {
            if (dba_exists($id, $this->dbhTables[$table])) {
                $result = dba_fetch($id, $this->dbhTables[$table]);
            }
        }

        if ($isLock) {
            $this->unlock();
        }

        return $result;
    }

    public function insert(string $table, string $id, string $val)
    {
        $this->writing('realInsert', $table, $id, $val);
    }

    public function update(string $table, string $id, string $val)
    {
        $this->writing('realUpdate', $table, $id, $val);
    }

    public function close()
    {
        $this->unlock();
        $this->lockFileClose();

        foreach($this->dbhTables as $dbh) {
            dba_close($dbh);
        }
    }

    private function writing(string $operation, string $table, string $id, string $val)
    {
        if (!$this->inTransaction()) {
            $this->lockEx();
            $this->$operation($table, $id, $val);
            $this->unlock();
        } else {
            $record = array();

            $record[self::TRANS_FD] = $table;
            $record[self::TRANS_OP] = $operation;
            $record[self::TRANS_ID] = $id;
            $record[self::TRANS_VAL] = $val;

            $cacheId = $table . $id;
            $this->transactionStack[$cacheId] = $record;
        }
    }

    private function realInsert(string $table, string $id, string $val)
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        if (!dba_insert($id, $val, $this->dbhTables[$table])) {
            throw new RuntimeException("Cannot insert record into " . $table);
        }
    }

    public function realUpdate(string $table, string $id, string $val)
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        if (!dba_replace($id, $val, $this->dbhTables[$table])) {
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