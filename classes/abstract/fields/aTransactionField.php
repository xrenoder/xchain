<?php


abstract class aTransactionField extends aField
{
    protected static $dbgLvl = Logger::DBG_TRANS_FLD;

    /** @var string  */
    protected $enumClass = 'TransactionFieldClassEnum'; /* overrided */

    public function getTransaction() : aTransaction {return $this->getParent();}

    public static function spawn(aTransaction $transaction, int $id, int $offset = 0) : self
    {
        /** @var aTransactionField $className */

        if ($className = TransactionFieldClassEnum::getClassName($id)) {
            return $className::create($transaction, $offset);
        }

        throw new Exception("Bad code - unknown transaction field classenum for ID " . $id);
    }
}