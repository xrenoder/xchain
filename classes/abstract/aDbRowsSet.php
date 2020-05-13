<?php


abstract class aDbRowsSet extends aBase implements constDbRowIds
{
    protected static $dbgLvl = Logger::DBG_ROW_SET;

    /**
     * 'propertyName' => 'rowClassName'
     * @var string[]
     */
    protected static $rows = array();   /* override me */

    abstract public static function create(App $app);

    public function fillRows() : self
    {
        $transKey = $this->dbTrans();

        foreach (static::$rows as $property => $className) {
            /** @var aDbRow $className */
            $this->$property = $className::create($this->getApp());
        }

        $this->dbCommit($transKey);

        $this->dbg(get_class($this) . " loaded");

        return $this;
    }
}