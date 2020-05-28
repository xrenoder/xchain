<?php


class PubKeyNodeByAddr extends aDbRow
{
    /** @var string  */
    protected static $table = self::PUBLIC_KEYS_NODES_TABLE;     /* overrided */

    /** @var string  */
    protected $idFormat = DbFieldClassEnum::ADDR; /* overrided */

    protected static $canBeReplaced = true;     /* overrided */

    /**
     * 'propertyName' => fieldFormat
     * @var array
     */
    protected static $fields = array(
        'nodeId' =>        DbFieldClassEnum::NODE,
        'publicKey' =>    DbFieldClassEnum::PUBKEY,
    );

    protected $nodeId = null;
    public function setNodeId($val, $needSave = true) : self {return $this->setNewValue($this->nodeId, $val, $needSave);}
    public function getNodeId() : ?int {return $this->nodeId;}

    protected $publicKey = null;
    public function setPublicKey($val, $needSave = true) : self {return $this->setNewValue($this->publicKey, $val, $needSave);}
    public function getPublicKey() : ?string {return $this->publicKey;}
}