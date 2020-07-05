<?php


abstract class aTransactionField extends aField
{
    protected static $dbgLvl = Logger::DBG_TRANS_FLD;

    /** @var string  */
    protected static $parentClass = 'aTransaction'; /* overrided */

    /** @var string  */
    protected static $enumClass = 'TransactionFieldClassEnum'; /* overrided */

    public function getTransaction() : aTransaction {return $this->getParent();}

    public function postPrepare() :  bool
    {
        $this->getTransaction()->addSignedData($this->getRawWithLength());

        return true;
    }
}