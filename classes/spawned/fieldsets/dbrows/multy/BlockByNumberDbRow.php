<?php


class BlockByNumberDbRow extends aMultyIdDbRow
{
    /** @var string  */
    protected $table = DbTableEnum::BLOCKS;     /* overrided */

    /** @var string  */
    protected $idFormatType = FieldFormatClassEnum::UBIG; /* overrided */

    /* 'property' => '[fieldType, false or object method]' or 'formatType' */
    protected static $fieldSet = array(
        'block' =>    [DbRowFieldClassEnum::BLOCK, 'getRaw'],
    );

    /** @var Block  */
    protected $block = null;
    public function setBlock(Block $val) : self {return $this->setNewValue($this->block, $val);}
    public function getBlock() : ?Block {return $this->block;}
}