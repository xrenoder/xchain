<?php


class PubKeyNodeByAddr extends aDbRow
{
    /** @var string  */
    protected static $table = self::PUBLIC_KEYS_NODES_TABLE;     /* overrided */

    /** @var string  */
    protected $idFormat = FieldFormatEnum::ADDR; /* overrided */

    protected static $canBeReplaced = true;     /* overrided */

    /**
     * 'propertyName' => fieldFormat
     * @var array
     */
    protected static $fields = array(
        'nodeId' =>        FieldFormatEnum::UCHAR,
        'publicKey' =>    FieldFormatEnum::PUBKEY,
    );

    protected $nodeId = null;
    public function getNodeId() : ?int {return $this->nodeId;}

    public function setNodeId($val, $needSave = true) : self
    {
        if ($val !== $this->nodeId) {
            $this->nodeId = $val;
            $this->saveIfNeed($needSave);
        }

        return $this;
    }

    protected $publicKey = null;
    public function getPublicKey() : ?string {return $this->publicKey;}

    public function setPublicKey($val, $needSave = true) : self
    {
        if ($val !== $this->publicKey) {
            $this->publicKey = $val;
            $this->saveIfNeed($needSave);
        }

        return $this;
    }
}