<?php


class TransactionMessage extends aSignedMessage
{
    /** @var int  */
    protected $dataClass = MessageDataClassEnum::TRANSACTION; /* overrided */

    public function setTransaction(aTransaction $val) : self {$this->getData()->setTransaction($val); return $this;}
    public function getTransaction() : aTransaction {return $this->getData()->getTransaction();}

    protected function incomingMessageHandler(): bool
    {
        throw new Exception($this->getName() . " Bad code - not defined method 'incomingMessageHandler()'");
    }
}