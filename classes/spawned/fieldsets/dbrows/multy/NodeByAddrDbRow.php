<?php


class NodeByAddrDbRow extends aMultyIdDbRow
{
    /** @var string  */
    protected $table = DbTableEnum::ADDR_NODES;     /* overrided */

    /** @var string  */
    protected $idFormatType = FieldFormatClassEnum::ADDR; /* overrided */

    /* 'property' => '[fieldType, false or object method]' or 'formatType' */
    protected static $fieldSet = array(
        'node' => [DbRowFieldClassEnum::NODE, 'getType']
    );

    /** @var aNode  */
    protected $node = null;
    public function setNode(aNode $val) : self {return $this->setNewValue($this->node, $val);}
    public function getNode() : ?aNode {return $this->node;}
}