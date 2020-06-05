<?php


abstract class aTransactionMessage extends aSignMessage
{
    /** @var aTransaction  */
    protected $transaction = null;
    public function setTransaction(aTransaction  $val) : self {$this->transaction = $val; return $this;}
    public function getTransaction() : aTransaction {return $this->transaction;}
}