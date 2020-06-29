<?php


abstract class aTransactionData extends aFieldSet
{
    protected static $dbgLvl = Logger::DBG_TRANS_DATA;

    /** @var string  */
    protected static $enumClass = 'TransactionDataClassEnum'; /* overrided */

    /** @var string  */
    protected $fieldClass = 'aTransactionDataField'; /* overrided */

    public function getTransaction() : aTransaction {return $this->getParent();}

    protected function __construct(aTransaction $parent)
    {
        parent::__construct($parent);
        $this->fields = array_replace($this->fields, static::$fieldSet);
    }

    protected static function create(aTransaction $parent) : self
    {
        $me = new static($parent);

        $me
            ->setTypeFromEnum();

        return $me;
    }
}