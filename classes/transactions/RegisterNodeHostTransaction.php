<?php


class RegisterNodeHostTransaction extends aTransactionCT
{
    /** @var int  */
    protected $dataClassId = TransactionDataClassEnum::NODE_HOST_NAME; /* overrided */

    public function setHost(string $val) : self {$this->data->setHost($val); return $this;}
    public function getHost() : ?string {return $this->data->getHost();}

    public function setNodeName(string $val) : self {$this->data->setNodeName($val); return $this;}
    public function getNodeName() : ?string {return $this->data->getNodeName();}
}