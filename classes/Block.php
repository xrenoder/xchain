<?php


class Block extends aFieldSet
{
    protected static $dbgLvl = Logger::DBG_BLOCK;

    /** @var string  */
    protected $fieldClass = 'aBlockField'; /* overrided */

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(      /* overrided */
        BlockFieldClassEnum::NUMBER =>      'blockNumber',
        BlockFieldClassEnum::CHAIN =>       'chainNumber',
        BlockFieldClassEnum::TIME =>        'blockTime',
        BlockFieldClassEnum::PREV_SIGN =>   'prevSignature',
        BlockFieldClassEnum::SIGNER_ADDR => 'signerAddrBin',
    );

    /** @var int  */
    protected $blockNumber = 0;
    public function getBlockNumber() : int {return $this->blockNumber;}

    /** @var int  */
    protected $chainNumber = 0;
    public function setChainNumber(int $val) : self {$this->chainNumber = $val; return $this;}
    public function getChainNumber() : int {return $this->chainNumber;}

    /** @var ?Block  */
    protected $prevBlock = null;
    public function setPrevBlock(Block $val) : self {$this->prevBlock = $val; $this->blockNumber = $val->getBlockNumber() + 1; $this->prevSignature = $val->getSignature(); return $this;}
    public function getPrevBlock() : ?Block {return $this->prevBlock;}

    /** @var string  */
    protected $prevSignature = '';
    public function getPrevSignature() : string {return $this->prevSignature;}

    /** @var int  */
    protected $blockTime = null;
    public function setBlockTime(int $val) : self {$this->blockTime = $val; return $this;}
    public function getBlockTime() : int {return $this->blockTime;}

    /** @var string  */
    protected $signerAddrBin = null;

    /** @var Address  */
    protected $signerAddress = null;
    public function setSignerAddress(Address $val) : self {$this->signerAddress = $val; $this->signerAddrBin = $val->getAddressBin(); return $this;}
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
//        $this->fields = array_diff_key($this->fields, static::$fieldLastSet);
        $this->fields = array_replace($this->fields, self::$fieldSet);
//        $this->fields = array_replace($this->fields, static::$fieldLastSet);

        $sections = BlockSectionClassEnum::getItemsList();

        foreach ($sections as $sectionId => $sectionClass) {
            $this->sections[$sectionId] = aBlockSection::spawn($this, $sectionId);
        }

//        $this->dbg($this->getName() .  ' fields:');
//        $this->dbg(var_export($this->fields, true));
    }

    public static function create(aBase $parent, Address $signerAddress, ?Block $prevBlock, int $chainNumber) : self
    {
        $me = new Block($parent);

        if ($prevBlock !== null) {
            $me->setPrevBlock($prevBlock);
        }

        if (!$signerAddress->isFull()) {
            throw new Exception("Block Bad code - signer address must have private key");
        }

        $me
            ->setChainNumber($chainNumber)
//            ->setBlockTime(time())
            ->setSignerAddress($signerAddress)
        ;

        $parent->dbg('Block ' . $me->getBlockNumber() . ' created');

        return $me;
    }

    public function addTransaction(aTransaction $transaction) : self
    {
        if ($transaction->getRaw() === null) {
            throw new Exception($this->getName() . " Bad code - transaction must have generated raw");
        }

        $transactionId = $transaction->getId();

        $sectionId = TransactionClassEnum::getBlockSectionId($transactionId);

        $this->sections[$sectionId]->addTransaction($transaction);

        return $this;
    }

    public function createRaw()
    {
        if (!$this->signerAddress->isFull()) {
            throw new Exception($this->getName() . " Bad code - address must be full for sign block");
        }

        if ($this->blockTime === null) {
            $this->blockTime = time();
        }

        $locator = $this->getLocator();

        $this->raw = '';

        foreach ($this->fields as $fieldId => $property) {
            if ($fieldId === BlockFieldClassEnum::FIRST_SECTION_INDEX) {
                break;
            }

            $formatId = BlockFieldClassEnum::getFormat($fieldId);
            $rawField = $locator->pack($formatId, $this->$property);
            $this->raw .= $rawField;
            $this->signedData .= $rawField;
        }

        foreach ($this->sections as $sectionId => $section) {
            $section->createRaw();
            $rawSection = $locator->pack(BlockFieldClassEnum::SECTION_LEN_FORMAT, $section->getRaw());
            $this->raw .= $rawSection;
            $this->signedData .= $rawSection;
        }

        $this->signature = $this->signerAddress->signBin($this->signedData);
        $rawSignature = SignBlockField::pack($this, $this->signature);
        $this->raw .= $rawSignature;

        $this->rawLength = strlen($this->raw);

        $this->dbg($this->getName() . " raw created ($this->rawLength bytes):\n" . bin2hex($this->raw) . "\n");

        return $this;
    }
}