<?php
/**
 * Base for storable objects classes (transactions, blocks, settings etc)
 */
class aDbRecord extends aBase
{
    /** @var string  */
    protected static $dbaTable = null; /* override me */

    protected $id = null;
    public function setId($val) : self {$this->id = $val; return $this;}
    public function getId() : string {return $this->id;}

    protected $data = null;
    public function setData($val) : self {$this->data = $val; return $this;}
    public function getData() : string {return $this->data;}

    protected function dbTrans()
    {
        $this->getApp()->getDba()->transactionBegin();
    }

    protected function dbCommit()
    {
        $this->getApp()->getDba()->transactionCommit();
    }

    protected function dbRollback()
    {
        $this->getApp()->getDba()->transactionRollback();
    }

    protected function first()
    {
        return $this->getApp()->getDba()->first(static::$dbaTable);
    }

    protected function next()
    {
        return $this->getApp()->getDba()->next(static::$dbaTable);
    }

    protected function check() : bool
    {
        return $this->getApp()->getDba()->check(static::$dbaTable, $this->id);
    }

    protected function load()
    {
        $this->data = $this->getApp()->getDba()->fetch(static::$dbaTable, $this->id);

        return $this->data;
    }

    protected function save(bool $replace = false)
    {
        if ($replace && $this->check()) {
            $this->getApp()->getDba()->update(static::$dbaTable, $this->id, $this->data);
        } else {
            $this->getApp()->getDba()->insert(static::$dbaTable, $this->id, $this->data);
        }
    }
}