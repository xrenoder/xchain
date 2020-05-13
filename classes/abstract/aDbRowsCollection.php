<?php


abstract class aDbRowsCollection extends aBase implements constDbRowIds
{
    /**
     * 'propertyName' => 'rowClassName'
     * @var string[]
     */
    protected static $rows = array();   /* override me */

    abstract public static function create(App $app);

    public function fillRows()
    {
        $transKey = $this->dbTrans();

        foreach (static::$rows as $property => $className) {
            /** @var aDbRow $className */
            $this->$property = $className::create($this->getApp());
        }

        $this->dbCommit($transKey);

        return $this;
    }
}