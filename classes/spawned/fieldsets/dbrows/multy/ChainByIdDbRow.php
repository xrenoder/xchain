<?php


class ChainByIdDbRow extends aMultyIdDbRow
{
    /** @var string  */
    protected $table = DbTableEnum::CHAINS;     /* overrided */

    /** @var string  */
    protected $idFormatType = FieldFormatClassEnum::UBIG; /* overrided */

    /* 'property' => '[fieldType, false or object method]' or 'formatType' */
    protected static $fieldSet = array(
        'chainName' =>                      FieldFormatClassEnum::ASIS_BYTE,
        'signerNode' =>                     [DbRowFieldClassEnum::NODE, 'getType'],
        'lastPreparedBlockId' =>            FieldFormatClassEnum::UBIG,
        'lastPreparedBlockTime' =>          FieldFormatClassEnum::ULONG,
        'lastPreparedBlockSignature' =>     FieldFormatClassEnum::ASIS_BYTE,
        'lastKnownBlockId' =>               FieldFormatClassEnum::UBIG,
    );

    /** @var string  */
    protected $chainName = null;
    public function setChainName(string &$val) : self {return $this->setNewValue($this->chainName, $val);}
    public function &getChainName() : ?string {return $this->chainName;}

    /** @var aNode  */
    protected $signerNode = null;
    public function setSignerNode(aNode $val) : self {return $this->setNewValue($this->signerNode, $val);}
    public function &getSignerNode() : ?aNode {return $this->signerNode;}

    /** @var int  */
    protected $lastPreparedBlockId = null;
    public function setLastPreparedBlockId(int &$val) : self {return $this->setNewValue($this->lastPreparedBlockId, $val);}
    public function &getLastPreparedBlockId() : ?int {return $this->lastPreparedBlockId;}

    /** @var int  */
    protected $lastPreparedBlockTime = null;
    public function setLastPreparedBlockTime(int &$val) : self {return $this->setNewValue($this->lastPreparedBlockTime, $val);}
    public function &getLastPreparedBlockTime() : ?int {return $this->lastPreparedBlockTime;}

    /** @var string  */
    protected $lastPreparedBlockSignature = null;
    public function setLastPreparedBlockSignature(string &$val) : self {return $this->setNewValue($this->lastPreparedBlockSignature, $val);}
    public function &getLastPreparedBlockSignature() : ?string {return $this->lastPreparedBlockSignature;}

    /** @var int  */
    protected $lastKnownBlockId = null;
    public function setLastKnownBlockId(int &$val) : self {return $this->setNewValue($this->lastKnownBlockId, $val);}
    public function &getLastKnownBlockId() : ?int {return $this->lastKnownBlockId;}
}