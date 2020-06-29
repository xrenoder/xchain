<?php


class NodeByAddr extends aMultyIdDbRow
{
    /** @var string  */
    protected $table = DbTableEnum::ADDR_NODES;     /* overrided */

    /** @var string  */
    protected $idFormatType = FieldFormatClassEnum::ADDR; /* overrided */

    /* 'property' => '[fieldType, false or object method]' or 'formatType' */
    protected static $fieldSet = array(
        'node' => [DbFieldClassEnum::NODE, 'getType']
    );

    /** @var aNode  */
    protected $node = null;
    public function setNode(aNode $val, $needSave = true) : self {return $this->setNewValue($this->node, $val, $needSave);}
    public function getNode() : ?aNode {return $this->node;}
}