<?php
/**
 * DBA operations
 */
class DBA extends aBase
{
// TODO сделать обработку ошибок
    protected static $dbgLvl = Logger::DBG_DBA;

    private const LOCK_EX_MODE = true;
    private const LOCK_SH_MODE = false;
    private const LOCK_UN_MODE = null;

    private const RECORD_FIELD = 'rec';
    private const OPERATION_FIELD = 'op';

    private const RECORD_TABLE = 'tab';
    private const RECORD_ID = 'id';
    private const RECORD_VAL = 'val';

    public const INTEGRITY_TABLE = DbTableEnum::INTEGRITY;

    private const INTEGRITY_HASH_ALGO = 'md4';
    private const INTEGRITY_LAST_RECORD_TABLE = 'lastRecordTable';
    private const INTEGRITY_LAST_RECORD_ID = 'lastRecordId';
    private const INTEGRITY_LAST_RECORD_VALUE = 'lastRecordValue';
    private const INTEGRITY_LAST_RECORD_HASH = 'lastRecordHash';

    private $integrityPassed = false;

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

    /** @var bool */
    private $transLockMode = null;

    /** @var bool  */
    private $transaction = false;
    public function inTransaction() : bool {return $this->transaction;}
    private $recordsStack = array();

    private $dbhTables = array();

    /**
     * @param App $locator
     * @param string $handler
     * @param string $dbExt
     * @param string $lockExt
     * @return self
     */
    public static function create(
        aLocator $locator,
        string $handler,
        string $dbPath,
        string $dbExt,
        string $lockFile,
        string $lockExt
    ) : self
    {
        $me = new static($locator);

        $me
            ->setDbaHandler($handler)
            ->setDbPath($dbPath)
            ->setDbExt($dbExt)
            ->setLockExt($lockExt)
            ->setLockFile($lockFile)

            ->getLocator()->setDba($me);

        $me->dbg("DBA created");

        return $me;
    }

    public function integrity() : bool
    {
        $integrityTable = self::INTEGRITY_TABLE;
        $integrityLastRecordValue = self::INTEGRITY_LAST_RECORD_VALUE;

        $this->lockEx();

        if ($this->check(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_HASH) === false) {
// initialize integrity records
            $val = 'init';

            $this->realInsert(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_TABLE, $integrityTable);
            $this->realInsert(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_ID, $integrityLastRecordValue);
            $this->realInsert(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_VALUE, $val);

            $this->realInsert(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_HASH, $this->hash($integrityTable, $integrityLastRecordValue, $val));
        }
// check DB for integrity

        $table = $this->fetch(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_TABLE);
        $id = $this->fetch(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_ID);
        $val = $this->fetch($table, $id);

        $hash = $this->fetch(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_HASH);

        $this->unlock();

        if ($hash === $this->hash($table, $id, $val)) {
            $this->integrityPassed = true;
        }

        return $this->integrityPassed;
    }

    public function removeDbTables() : void
    {
        $tables = DbTableEnum::getItemsList();

        foreach($tables as $table) {
            $tableFile = $this->getTableFile($table);
            unlink($tableFile);
        }
    }

    public function transactionBegin() : void
    {
        if ($this->transaction) {
            throw new Exception("DBA Bad code - cannot begin DB-transaction, when other DB-transaction is opened");
        }

        $this->transaction = true;

        $this->lockSh();

        $this->dbg("DB transaction begin");
    }

    public function transactionRollback() : void
    {
        if (!$this->transaction) {
            throw new Exception("DBA Bad code - cannot rollback DB-transaction already closed");
        }

        if (count($this->recordsStack)) {
            $this->recordsStack = array();
        }

        $this->transaction = false;
        $this->unlock();

        $this->dbg("DB transaction rollback");
    }

    public function transactionCommit() : void
    {
        if (!$this->transaction) {
            throw new Exception("DBA Bad code - cannot commit DB-transaction already closed");
        }

        $this->dbg("DB transaction will be commited");

        $table = null;
        $id = null;
        $val = null;

        $recordsCounter = 0;

        if (count($this->recordsStack)) {
            $this->lockEx();
        }

        foreach($this->recordsStack as $record) {
            $operation = $record[self::OPERATION_FIELD];

            $table = $record[self::RECORD_FIELD][self::RECORD_TABLE];
            $id = $record[self::RECORD_FIELD][self::RECORD_ID];
            $val = $record[self::RECORD_FIELD][self::RECORD_VAL];

// fill data for integrity checking
            $recordsCounter++;

            if ($recordsCounter === 1) {
                $this->realUpdate(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_TABLE, $table);
                $this->realUpdate(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_ID, $id);
            }

            $this->$operation($table, $id, $val);
        }

        if ($recordsCounter > 1) {
            $this->realUpdate(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_TABLE, $table);
            $this->realUpdate(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_ID, $id);
        }

        if ($recordsCounter > 0) {
            $this->realUpdate(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_HASH, $this->hash($table, $id, $val));
        }

        $this->unlock();

        if (count($this->recordsStack)) {
            $this->recordsStack = array();
        }

        $this->dbg("DB transaction commit suss");

        $this->transaction = false;
    }

    public function first(string $table) : string
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        if (!$this->transaction) {
            $this->lockSh();
        }

        $id = dba_firstkey($this->dbhTables[$table]);

        if (!$this->transaction) {
            $this->unlock();
        }

        return $id;
    }

    public function next(string $table) : string
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        if (!$this->transaction) {
            $this->lockSh();
        }

        $id = dba_nextkey($this->dbhTables[$table]);

        if (!$this->transaction) {
            $this->unlock();
        }

        return $id;
    }

    public function check(string $table, string $id, $useCache = true) : bool
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        $result = null;

        if (!$this->transaction) {
            $this->lockSh();
        } else {

            if ($useCache) {
                $cacheId = $this->getCacheId($table, $id);

                if (isset($this->recordsStack[$cacheId])) {
                    $result = true;
                }
            }
        }

        if ($result === null) {
            $result = dba_exists($id, $this->dbhTables[$table]);
        }

        if (!$this->transaction) {
            $this->unlock();
        }

        return $result;
    }

    public function &fetch(string $table, string $id, $useCache = true) : ?string
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        $result = null;

        if (!$this->transaction) {
            $this->lockSh();
        } else {

            if ($useCache) {
                $cacheId = $this->getCacheId($table, $id);

                if (isset($this->recordsStack[$cacheId])) {
                    $result = $this->recordsStack[$cacheId][self::RECORD_FIELD][self::RECORD_VAL];
                }
            }
        }

        if ($result === null) {
            if (dba_exists($id, $this->dbhTables[$table])) {
                $result = dba_fetch($id, $this->dbhTables[$table]);
            }
        }

        if (!$this->transaction) {
            $this->unlock();
        }

        return $result;
    }

    public function insert(string $table, string $id, string &$val) : void
    {
        $this->write('realInsert', $table, $id, $val);
    }

    public function update(string $table, string $id, string &$val) : void
    {
        $this->write('realUpdate', $table, $id, $val);
    }

    public function close() : void
    {
        $this->unlock();
        $this->lockFileClose();

        foreach($this->dbhTables as $dbh) {
            dba_close($dbh);
        }
    }

    private function write(string $operation, string $table, string $id, string &$val) : void
    {
        if (!$this->transaction) {
            $this->lockEx();
            $this->$operation($table, $id, $val);
            $this->unlock();
        } else {
            $record = array();

            $record[self::RECORD_TABLE] = $table;
            $record[self::RECORD_ID] = $id;
            $record[self::RECORD_VAL] = $val;

            $cacheId = $this->getCacheId($table, $id);

            if (!isset($this->recordsStack[$cacheId])) {
                $this->recordsStack[$cacheId][self::OPERATION_FIELD] = $operation;
            }

            $this->recordsStack[$cacheId][self::RECORD_FIELD] = $record;

        }
    }

    private function realInsert(string $table, string $id, string &$val) : void
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        if (!dba_insert($id, $val, $this->dbhTables[$table])) {
            throw new RuntimeException("Cannot insert record into " . $table);
        }
    }

    public function realUpdate(string $table, string $id, string &$val) : void
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        if (!dba_replace($id, $val, $this->dbhTables[$table])) {
            throw new RuntimeException("Cannot replace record in " . $table);
        }
    }

    private function tableOpen(string $table) : void
    {
        $tableFile = $this->getTableFile($table);

        if(!$this->dbhTables[$table] = dba_open($tableFile, static::$dbaMode, $this->dbaHandler)) {
            throw new RuntimeException("Cannot open table file " . $tableFile);
        }
    }

    private function lockEx(bool $checkOnly = false) : bool
    {
        if (!$this->fdDbLock) $this->lockFileOpen();
        else if ($this->transLockMode === self::LOCK_EX_MODE) return true;

        if ($checkOnly) {
            $flag = LOCK_EX | LOCK_NB;
        }  else {
            $flag = LOCK_EX;
        }

        $this->transLockMode = self::LOCK_EX_MODE;

        return flock($this->fdDbLock, $flag);
    }

    private function lockSh(bool $checkOnly = false) : bool
    {
        if (!$this->fdDbLock) $this->lockFileOpen();
        else if ($this->transLockMode !== self::LOCK_UN_MODE) return true;

        if ($checkOnly) {
            $flag = LOCK_SH | LOCK_NB;
        }  else {
            $flag = LOCK_SH;
        }

        $this->transLockMode = self::LOCK_SH_MODE;

        return flock($this->fdDbLock, $flag);
    }

    private function unlock() : bool
    {
        if (!$this->fdDbLock) $this->lockFileOpen();
        else if ($this->transLockMode === self::LOCK_UN_MODE) return true;

        $this->transLockMode = self::LOCK_UN_MODE;

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

    private function &hash(string &$table, string &$id, string &$val) : string
    {
        $result = hash(self::INTEGRITY_HASH_ALGO, $table . "_" . $id . "_" . $val, true);
        return $result;
    }

    private function getCacheId(string $table, string $id) : string
    {
        return $table . "*" . $id;
    }

    private function getTableFile(string $table) : string
    {
        return $this->dbPath . $table . $this->dbExt;
    }
}