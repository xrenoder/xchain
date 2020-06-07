<?php


class NodeHostNameTransactionData extends aTransactionData
{
    /**
     * 'propertyName' => fieldFormat
     * @var array
     */
    protected static $fieldSet = array(
        'host' =>        TransactionDataFieldClassEnum::HOST,
        'nodeName' =>    TransactionDataFieldClassEnum::NODE_NAME,
    );

    /** @var string  */
    protected $host = null;
    public function setHost(string $val) : self {$this->host = $val; return $this;}
    public function getHost() : ?string {return $this->host;}

    /** @var string  */
    protected $nodeName = null;
    public function setNodeName(string $val) : self {$this->nodeName = $val; return $this;}
    public function getNodeName() : ?string {return $this->nodeName;}
}