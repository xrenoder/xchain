<?php


class NodeByAddrDbRow extends aMultyIdDbRow
{
    /** @var string  */
    protected $table = DbTableEnum::NODE_TYPES;     /* overrided */

    /** @var string  */
    protected $idFormatType = FieldFormatClassEnum::ADDR; /* overrided */

    /* 'property' => '[fieldType, false or object method]' or 'formatType' */
    protected static $fieldSet = array(
        'nodeType' => FieldFormatClassEnum::UBYTE,
    );

    /** @var int  */
    protected $nodeType = null;
    public function setNodeType(int $val) : self {return $this->setNewValue($this->nodeType, $val);}
    public function getNodeType() : ?int {return $this->nodeType;}
}