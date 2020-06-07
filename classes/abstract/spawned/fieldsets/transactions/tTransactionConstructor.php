<?php


trait tTransactionConstructor
{
    protected function __construct(aBase $parent)
    {
        parent::__construct($parent);
        $this->fields = array_diff_key($this->fields, static::$fieldLastSet);
        $this->fields = array_replace($this->fields, self::$fieldSet);
        $this->fields = array_replace($this->fields, static::$fieldLastSet);

//        $this->dbg($this->getName() .  ' fields:');
//        $this->dbg(var_export($this->fields, true));
    }
}