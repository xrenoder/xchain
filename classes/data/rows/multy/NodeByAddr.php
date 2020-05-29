<?php


class NodeByAddr extends aMultyIdDbRow
{
    /** @var string  */
    protected $table = self::ADDR_NODES_TABLE;     /* overrided */

    /** @var string  */
    protected $idFormat = DbFieldClassEnum::ADDR; /* overrided */

    protected $canBeReplaced = true;     /* overrided */

    /**
     * 'propertyName' => fieldFormat
     * @var array
     */
    protected static $fieldSet = array(
        'nodeId' =>        DbFieldClassEnum::NODE,
    );

    protected $nodeId = null;
    public function setNodeId($val, $needSave = true) : self {return $this->setNewValue($this->nodeId, $val, $needSave);}
    public function getNodeId() : ?int {return $this->nodeId;}
}