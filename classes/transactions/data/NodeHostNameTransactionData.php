<?php


class NodeHostNameTransactionData extends aTransactionData
{
    /* 'property' => '[fieldType, isObject]' or 'formatType' */
    protected static $fieldSet = array(
        'host' => [TransactionDataFieldClassEnum::HOST, 'getPair'],
        'nodeName' => [TransactionDataFieldClassEnum::NODE_NAME, false],
    );

    /** @var Host  */
    protected $host = null;
    public function setHost(Host $val) : self {$this->host = $val; return $this;}
    public function getHost() : Host {return $this->host;}

    /** @var string  */
    protected $nodeName = null;
    public function setNodeName(string $val) : self {$this->nodeName = $val; return $this;}
    public function getNodeName() : string {return $this->nodeName;}
}