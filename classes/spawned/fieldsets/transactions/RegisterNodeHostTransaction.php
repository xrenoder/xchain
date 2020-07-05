<?php


class RegisterNodeHostTransaction extends aTransactionCS
{
    /** @var int  */
    protected $dataClass = TransactionDataClassEnum::NODE_HOST_NAME; /* overrided */

    public function setHost(Host $val) : self {$this->getData()->setHost($val); return $this;}
    public function getHost() : Host {return $this->getData()->getHost();}

    public function setNodeName(string $val) : self {$this->getData()->setNodeName($val); return $this;}
    public function getNodeName() : string {return $this->getData()->getNodeName();}
}