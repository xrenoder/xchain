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

    private const INSERT_OPERATION = 'rI';
    private const UPDATE_OPERATION = 'rU';
    private const DELETE_OPERATION = 'rD';

    private const FIRST_OPERATION = 'rF';
    private const NEXT_OPERATION = 'rN';

    private const RECORD_TABLE = 'tab';
    private const RECORD_ID = 'id';
    private const RECORD_VAL = 'val';
    private const RECORD_FIRST_OPERATION = 'fop';

    public const INTEGRITY_TABLE = DbTableEnum::INTEGRITY;

    private const INTEGRITY_HASH_ALGO = 'md4';
    private const INTEGRITY_LAST_RECORD_TABLE = 'lastRecordTable';
    private const INTEGRITY_LAST_RECORD_ID = 'lastRecordId';
    private const INTEGRITY_LAST_RECORD_VALUE = 'lastRecordValue';
    private const INTEGRITY_LAST_RECORD_HASH = 'lastRecordHash';

    private $integrityPassed = false;

    protected static $dbaMode = "cd";

    /** @var string  */
    private $deleteValue = "DeLeTeD";

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
    private $recordsByTables = array();
    private $tablePointers = array();
    private $optimizeTables = array();

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

            $this->rI(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_TABLE, $integrityTable);
            $this->rI(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_ID, $integrityLastRecordValue);
            $this->rI(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_VALUE, $val);

            $this->rI(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_HASH, $this->hash($integrityTable, $integrityLastRecordValue, $val));
        }
// check DB for integrity

        $table = $this->fetch(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_TABLE);
        $id = $this->fetch(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_ID);
        $val = $this->fetch($table, $id);

        if ($val === null) {
            $val = $this->deleteValue;
        }

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

        $this->closeTransaction();

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
                $this->rU(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_TABLE, $table);
                $this->rU(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_ID, $id);
            }

            $this->$operation($table, $id, $val);
        }

        if ($recordsCounter > 1) {
            $this->rU(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_TABLE, $table);
            $this->rU(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_ID, $id);
        }

        if ($recordsCounter > 0) {
            $this->rU(self::INTEGRITY_TABLE, self::INTEGRITY_LAST_RECORD_HASH, $this->hash($table, $id, $val));
        }

        if (count($this->optimizeTables)) {
            foreach ($this->optimizeTables as $handle) {
                $this->optimize($handle);
            }

            $this->optimizeTables = array();
        }

        $this->closeTransaction();

        $this->dbg("DB transaction commit suss");
    }

    public function &first(string $table) : ?string
    {
        return $this->firstOrNext($table, self::FIRST_OPERATION);
    }

    public function &next(string $table) : ?string
    {
        return $this->firstOrNext($table, self::NEXT_OPERATION);
    }

    public function &firstOrNext(string $table, string $operation) : ?string
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        if ($operation === self::FIRST_OPERATION) {
            if (isset($this->tablePointers[$table])) {
                unset($this->tablePointers[$table]);
            }
        }

        if (!$this->transaction) {
            $this->lockSh();

            if ($operation === self::FIRST_OPERATION) {
                $id = dba_firstkey($this->dbhTables[$table]);
            } else {
                $id = dba_nextkey($this->dbhTables[$table]);
            }

            $this->unlock();
        } else {
            if (!isset($this->tablePointers[$table])) {
                $searchInDb = true;
                $searchInCache = false;
            } else {
                $searchInDb = false;
                $searchInCache = true;
            }

            $needSearch = true;

            while($needSearch) {
                if ($searchInDb) {
                    if ($operation === self::FIRST_OPERATION) {
                        $id = dba_firstkey($this->dbhTables[$table]);
                    } else {
                        $id = dba_nextkey($this->dbhTables[$table]);
                    }

                    $operation = self::NEXT_OPERATION;

                    if ($id !== false) {
                        if (!isset($this->recordsByTables[$table][$id]) || $this->recordsByTables[$table][$id] !== self::DELETE_OPERATION) {
                            $needSearch = false;
                        }
                    } else {
                        $searchInDb = false;
                        $searchInCache = true;
                    }
                }

                if ($searchInCache) {
                    if (!isset($this->recordsByTables[$table])) {
                        $needSearch = false;
                    } else if ($this->tablePointers[$table] === false) {
                        $needSearch = false;
                    } else {
                        if (!isset($this->tablePointers[$table])) {
                            $this->tablePointers[$table] = true;
                            reset($this->recordsByTables[$table]);
                        } else {
                            next($this->recordsByTables[$table]);
                        }

                        $id = key($this->recordsByTables[$table]);

                        if ($id !== null) {
                            if (current($this->recordsByTables[$table]) !== self::DELETE_OPERATION) {
                                $needSearch = false;
                            }
                        } else {
                            $this->tablePointers[$table] = false;
                            $needSearch = false;
                        }
                    }
                }
            }
        }

        if ($id === false) {
            $id = null;
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
                    if ($this->recordsStack[$cacheId][self::OPERATION_FIELD] === self::DELETE_OPERATION) {
                        $result = false;
                    } else {
                        $result = true;
                    }
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
        $isDeleted = false;

        if (!$this->transaction) {
            $this->lockSh();
        } else {

            if ($useCache) {
                $cacheId = $this->getCacheId($table, $id);

                if (isset($this->recordsStack[$cacheId])) {
                    if ($this->recordsStack[$cacheId][self::OPERATION_FIELD] === self::DELETE_OPERATION) {
                        $isDeleted = true;
                    } else {
                        $result = $this->recordsStack[$cacheId][self::RECORD_FIELD][self::RECORD_VAL];
                    }
                }
            }
        }

        if ($result === null && !$isDeleted) {
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
        $this->write(self::INSERT_OPERATION, $table, $id, $val);
    }

    public function update(string $table, string $id, string &$val) : void
    {
        $this->write(self::UPDATE_OPERATION, $table, $id, $val);
    }

    public function delete(string $table, string $id) : void
    {
        $this->write(self::DELETE_OPERATION, $table, $id, $this->deleteValue);
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

            $removeRecord = false;

            if (!isset($this->recordsStack[$cacheId])) {
                $this->recordsStack[$cacheId][self::OPERATION_FIELD] = $operation;
                $record[self::RECORD_FIRST_OPERATION] = $operation;
            } else {
                $record[self::RECORD_FIRST_OPERATION] = $this->recordsStack[$cacheId][self::RECORD_FIELD][self::RECORD_FIRST_OPERATION];

                if ($operation === self::INSERT_OPERATION) {
                    if ($record[self::RECORD_FIRST_OPERATION] === self::DELETE_OPERATION) {
                        $this->recordsStack[$cacheId][self::OPERATION_FIELD] = self::UPDATE_OPERATION;
                    } else {
                        throw new RuntimeException("Cannot insert record into $table after " . $record[self::RECORD_FIRST_OPERATION]);
                    }
                } else if ($operation === self::UPDATE_OPERATION) {
                    if ($record[self::RECORD_FIRST_OPERATION] === self::DELETE_OPERATION) {
                        $this->recordsStack[$cacheId][self::OPERATION_FIELD] = self::UPDATE_OPERATION;
                    }
                } else if ($operation === self::DELETE_OPERATION) {
                    if ($record[self::RECORD_FIRST_OPERATION] === self::DELETE_OPERATION) {
                        throw new RuntimeException("Cannot delete record in $table after realDelete");
                    }

                    if ($record[self::RECORD_FIRST_OPERATION] === self::INSERT_OPERATION) {
                        $removeRecord = true;
                    } else if ($record[self::RECORD_FIRST_OPERATION] === self::UPDATE_OPERATION) {
                        $this->recordsStack[$cacheId][self::OPERATION_FIELD] = self::DELETE_OPERATION;
                    }
                }
            }

            if ($removeRecord) {
                unset($this->recordsStack[$cacheId]);

                if (isset($this->recordsByTables[$table][$id])) {
                    unset($this->recordsByTables[$table][$id]);
                }
            } else {
                $this->recordsStack[$cacheId][self::RECORD_FIELD] = $record;

                if ($this->recordsStack[$cacheId][self::OPERATION_FIELD] !== self::UPDATE_OPERATION) {
                    if (!isset($this->recordsByTables[$table])) {
                        $this->recordsByTables[$table] = array();
                    }

                    $this->recordsByTables[$table][$id] = $this->recordsStack[$cacheId][self::OPERATION_FIELD];
                }
            }
        }
    }

    private function rI(string $table, string $id, string &$val) : void
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        if (!dba_insert($id, $val, $this->dbhTables[$table])) {
            throw new RuntimeException("Cannot insert record into " . $table);
        }
    }

    private function rU(string $table, string $id, string &$val) : void
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        $handle = $this->dbhTables[$table];

        if (!dba_replace($id, $val, $handle)) {
            throw new RuntimeException("Cannot replace record in " . $table);
        }

        if  ($this->transaction) {
            $this->optimizeTables[$table] = $handle;
        } else {
            $this->optimize($handle);
        }
    }

    private function rD(string $table, string $id, string &$val) : void
    {
        if (!isset($this->dbhTables[$table])) $this->tableOpen($table);

        $handle = $this->dbhTables[$table];

        if (!dba_delete($id, $handle)) {
            throw new RuntimeException("Cannot delete record in " . $table);
        }

        if  ($this->transaction) {
            $this->optimizeTables[$table] = $handle;
        } else {
            $this->optimize($handle);
        }
    }

    private function optimize($handle) : void
    {
        dba_sync($handle);
        dba_optimize($handle);
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

    private function closeTransaction() : void
    {
        if (count($this->recordsStack)) {
            $this->recordsStack = array();
        }

        if (count($this->recordsByTables)) {
            $this->recordsByTables = array();
        }

        if (count($this->tablePointers)) {
            $this->tablePointers = array();
        }

        $this->transaction = false;

        $this->unlock();
    }
}