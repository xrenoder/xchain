<?php


abstract class aDbRowsSet extends aBase
{
    protected static $dbgLvl = Logger::DBG_DB_RSET;

    /**
     * 'rowId' => 'propertyName'
     * @var string[]
     */
    protected static $rows = array();   /* override me */

    abstract public static function create(aLocator $locator);

    public function fillRows() : self
    {
        $dbTransactionKey = $this->dbTrans();

        foreach (static::$rows as $rowId => $property) {
            $this->$property = aFixedIdDbRow::spawn($this->getLocator(), $rowId);
        }

        $this->dbCommit($dbTransactionKey);

        $this->dbg(get_class($this) . " loaded");

        return $this;
    }
}