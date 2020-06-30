<?php


class Block extends aFieldSet
{
    protected static $dbgLvl = Logger::DBG_BLOCK;

    /** @var string  */
    protected $fieldClass = 'aBlockField'; /* overrided */

    /* 'property' => '[fieldType, false or object method]' or 'formatType' */
    protected static $fieldSet = array(      /* overrided */
        'blockNumber' =>    FieldFormatClassEnum::UBIG,
        'chain' =>          [BlockFieldClassEnum::CHAIN, 'getId'],
        'blockTime' =>      [BlockFieldClassEnum::TIME, false],
        'signerAddress' =>  [BlockFieldClassEnum::SIGNER_ADDR, 'getAddressBin'],
    );

    /** @var int  */
    protected $id = 0;
    public function getId() : int {return $this->id;}

    /** @var Chain  */
    protected $chain = null;
    public function setChain(Chain $val) : self {$this->chain = $val; return $this;}
    public function getChain() : Chain {return $this->chain;}

    /** @var Block  */
    protected $prevBlock = null;
    public function setPrevBlock(Block $val) : self {$this->prevBlock = $val; $this->id = $val->getId() + 1; $this->prevSignature = $val->getSignature(); return $this;}
    public function getPrevBlock() : ?Block {return $this->prevBlock;}

    public function getPrevSignature() : string {return $this->prevBlock->getSignature();}

    /** @var int  */
    protected $blockTime = null;
    public function setBlockTime(int $val) : self {$this->blockTime = $val; return $this;}
    public function getBlockTime() : int {return $this->blockTime;}

    /** @var Address  */
    protected $signerAddress = null;
    public function setSignerAddress(Address $val) : self {$this->signerAddress = $val; return $this;}
    public function getSignerAddress() : Address {return $this->signerAddress;}

    protected static $fieldLastSet = array(  /* overrided */
        TransactionFieldClassEnum::SIGN =>      'signature',
    );

    /** @var string  */
    protected $signature = null;
    public function getSignature() : string {return $this->signature;}

    /** @var string  */
    protected $signedData = '';
    public function getSignedData() : string {return $this->signedData;}

    /** @var aBlockSection[] */
    protected $sections = array();

    protected function __construct(aBase $parent)
    {
        parent::__construct($parent);
        $this->fields = array_replace($this->fields, self::$fieldSet);

        $sections = BlockSectionClassEnum::getItemsList();

        foreach ($sections as $sectionType => $sectionClass) {
            $this->sections[$sectionType] = aBlockSection::spawn($this, $sectionType);
        }

//        $this->dbg($this->getName() .  ' fields:');
//        $this->dbg(var_export($this->fields, true));
    }

    public static function create(aBase $parent, Chain $chain, Address $signerAddress, ?Block $prevBlock) : self
    {
        $me = new Block($parent);

        if ($prevBlock !== null) {
            $me->setPrevBlock($prevBlock);
        }

        if (!$signerAddress->isFull()) {
            throw new Exception("Block Bad code - signer address must have private key");
        }

        $me
            ->setChain($chain)
            ->setSignerAddress($signerAddress)
        ;

        $parent->dbg('Block ' . $me->getId() . ' created');

        return $me;
    }

    public static function createFromRaw(aBase $parent, string &$raw) : self
    {
        $me = new Block($parent);

        throw new Exception("Block Bad code - createFromRaw must be defined");
    }

    public function addTransaction(aTransaction $transaction) : self
    {
        if ($transaction->getRaw() === null) {
            throw new Exception($this->getName() . " Bad code - transaction must have generated raw");
        }

        $transactionType = $transaction->getType();

        $sectionType = TransactionClassEnum::getBlockSectionId($transactionType);

        $this->sections[$sectionType]->addTransaction($transaction);

        return $this;
    }

    public function createRaw() : aFieldSet
    {
        if (!$this->signerAddress->isFull()) {
            throw new Exception($this->getName() . " Bad code - address must be full for sign block");
        }

        if ($this->blockTime === null) {
            $this->blockTime = time();
        }

        $this->raw = '';

        foreach ($this->fields as $fieldId => $property) {
            $formatId = BlockFieldClassEnum::getFormat($fieldId);
            $this->raw .= $this->simplePack($formatId, $this->$property);

            if ($fieldId === BlockFieldClassEnum::FIRST_SECTION_REACHED) {
                break;
            }
        }

        foreach ($this->sections as $sectionType => $section) {
            $section->createRaw();
            $this->raw .= $section->getRaw();
        }

        $this->signature = $this->signerAddress->signBin($this->raw);
        $rawSignature = SignBlockField::pack($this, $this->signature);
        $this->raw .= $rawSignature;

        $this->rawLength = strlen($this->raw);

        $this->dbg($this->getName() . " raw created ($this->rawLength bytes):\n" . bin2hex($this->raw) . "\n");

        return $this;
    }

    public function parseRaw() : void
    {
        foreach ($this->fields as $fieldId => $property) {
            if ($this->fieldPointer > $fieldId) {
                continue;
            }

            if (!$this->prepareField($fieldId, $property)) {
// if field cannot be prepared - break  (not 'return false'), may be all formats was prepared
                break;
            }

            if ($fieldId === BlockFieldClassEnum::FIRST_SECTION_REACHED) {
                break;
            }
        }

// check unpack maxLength or maxValue or fixLength error
        if ($this->field !== null && $this->field->getLength() === null && $this->field->getValue() === null) {
            return;
        }

        foreach ($this->sections as $sectionId => $section) {

        }
    }

    protected function prepareSection(int $sectionId) : bool
    {

    }
}