<?php


class TransactionMessageData extends aMessageData
{
    /* 'property' => '[fieldType, objectMethod]' or 'formatType' */
    protected static $fieldSet = array(
        'transaction' => [MessageDataFieldClassEnum::TRANSACTION, 'getRaw'],
    );

    /** @var aTransaction  */
    protected $transaction = null;
    public function setTransaction(aTransaction $val) : self {$this->transaction = $val; return $this;}
    public function getTransaction() : aTransaction {return $this->transaction;}
}