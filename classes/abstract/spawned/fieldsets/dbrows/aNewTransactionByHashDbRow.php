<?php


abstract class aNewTransactionByHashDbRow extends aMultyIdDbRow
{
    /** @var string  */
    protected $idFormatType = FieldFormatClassEnum::MD4_RAW; /* overrided */

    /* 'property' => '[fieldType, false or object method]' or 'formatType' */
    protected static $fieldSet = array(
        'transaction' =>    [DbRowFieldClassEnum::TRANSACTION, 'getRaw'],
    );

    /** @var aTransaction  */
    protected $transaction = null;
    public function setTransaction(aTransaction $val) : self {return $this->setNewValue($this->transaction, $val);}
    public function getTransaction() : ?aTransaction {return $this->transaction;}
}