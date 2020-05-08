<?php
/**
 * Base for storable objects classes (transactions, blocks, settings etc)
 */
class aDbRecord extends aBase
{
    /** @var string  */
    protected $table = null;
    public function setTable($val) : self {$this->table = $val; return $this;}

    protected $id = null;
//    public function setId($val) : self {$this->id = $val; return $this;}
//    public function getId() : string {return $this->id;}

    protected $data = null;
//    public function setData($val) : self {$this->data = $val; return $this;}
//    public function getData() : string {return $this->data;}

    protected $fields = array();  /* override me */

    public function fillFields()
    {
        $transKey = $this->dbTrans();

        $rowCnt = 0;

        if ($this->id = $this->first()) {
            do {
                $this->fields[$this->id] = $this->load();
                $rowCnt++;
            } while ($this->id = $this->next());
        }

        if ($rowCnt !== count($this->fields)) {
            foreach($this->fields as $this->id => $this->data) {
                $this->save(true);
            }
        }

        $this->dbCommit($transKey);

        return $this;
    }

    protected function dbTrans() : string
    {
        return $this->getApp()->getDba()->transactionBegin();
    }

    protected function dbCommit(string $transactionKey)
    {
        $this->getApp()->getDba()->transactionCommit($transactionKey);
    }

    protected function first()
    {
        return $this->getApp()->getDba()->first($this->table);
    }

    protected function next()
    {
        return $this->getApp()->getDba()->next($this->table);
    }

    protected function check() : bool
    {
        return $this->getApp()->getDba()->check($this->table, $this->id);
    }

    protected function load()
    {
        $this->data = $this->getApp()->getDba()->fetch($this->table, $this->id);

        return $this->data;
    }

    protected function save(bool $replace = false)
    {
        if ($replace && $this->check()) {
            $this->getApp()->getDba()->update($this->table, $this->id, $this->data);
        } else {
            $this->getApp()->getDba()->insert($this->table, $this->id, $this->data);
        }
    }

    protected function setAndSaveField($fieldId, $val) : self
    {
        $this->id = $fieldId;
        $this->fields[$fieldId] = $val;
        $this->data = $val;
        $this->save(true);

        return $this;
    }
}