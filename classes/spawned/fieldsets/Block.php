<?php


class Block extends aFieldSet
{
    protected static $dbgLvl = Logger::DBG_BLOCK;

    /** @var string  */
    protected $fieldClass = 'aBlockField'; /* overrided */

    /* 'property' => '[fieldType, false or object method]' or 'formatType' */
    protected static $fieldSet = array(      /* overrided */
        'id' =>             [BlockFieldClassEnum::ID, false],
        'chain' =>          [BlockFieldClassEnum::CHAIN, 'getId'],
        'time' =>           [BlockFieldClassEnum::TIME, false],
        'signerAddress' =>  [BlockFieldClassEnum::SIGNER, 'getAddressBin'],
        'signature' =>      [BlockFieldClassEnum::SIGN, false],
    );

    protected const SECTIONS_REACHED = 'signerAddress';
    protected const SIGN_REACHED = 'signature';

    /** @var int  */
    protected $id = 0;
    public function setId(int $val) : self {$this->id = $val; return $this;}
    public function getId() : int {return $this->id;}

    /** @var ChainByIdDbRow  */
    protected $chain = null;
    public function setChain(ChainByIdDbRow $val) : self {$this->chain = $val; return $this;}
    public function getChain() : ChainByIdDbRow {return $this->chain;}

    /** @var int  */
    protected $time = null;
    public function setTime(int $val) : self {$this->time = $val; return $this;}
    public function getTime() : int {return $this->time;}

    /** @var Address  */
    protected $signerAddress = null;
    public function setSignerAddress(Address $val) : self {$this->signerAddress = $val; return $this;}
    public function getSignerAddress() : Address {return $this->signerAddress;}

    /** @var string  */
    protected $signature = null;
    public function getSignature() : string {return $this->signature;}

    /** @var string  */
    protected $signedData = '';
    public function setSignedData(string &$val) : self {$this->signedData = $val; return $this;}
    public function addSignedData(string &$val) : self {$this->signedData .= $val; return $this;}
    public function &getSignedData() : string {return $this->signedData;}

    /** @var aBlockSection[] */
    protected $sections = array();

    /** @var bool  */
    protected $alreadySaved = false;
    public function setAlreadySaved(bool $val) : self {$this->alreadySaved = $val; return $this;}
    public function isAlreadySaved() : bool {return $this->alreadySaved;}

    /** @var bool  */
    protected $prepareLater = false;
    public function setPrepareLater(bool $val) : self {$this->prepareLater = $val; return $this;}
    public function isPrepareLater() : bool {return $this->prepareLater;}

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

    public static function createNew(aBase $parent, ChainByIdDbRow $chain, Address $signerAddress) : self
    {
        $me = new Block($parent);

        if (!$signerAddress->isFull()) {
            throw new Exception("Block Bad code - signer address must have private key");
        }

        if ($chain->getLastPreparedBlockSignature() === '') {
            $me->setId($chain->getLastPreparedBlockId() + 1);
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
        $me
            ->setRaw($raw)
            ->parseRaw()
        ;

        return $me;
    }

    public function addTransaction(aTransaction $transaction) : self
    {
        $transactionType = $transaction->getType();

        $sectionType = TransactionClassEnum::getBlockSectionType($transactionType);

        $this->sections[$sectionType]->addTransaction($transaction);

        return $this;
    }

    public function creatingRawPre() : void
    {
        if (!$this->signerAddress->isFull()) {
            throw new Exception($this->getName() . " Bad code - address must be full for sign block");
        }

        if ($this->time === null) {
            $this->time = time();
        }
    }

    public function creatingRawPreInterrupt(string &$property) : void
    {
        if ($property !== static::SIGN_REACHED) {
            $this->$property = $this->getSignerAddress()->signBin($this->raw);
        }
    }

    public function creatingRawPostInterrupt(string &$property) : void
    {
        if ($property === static::SECTIONS_REACHED) {
            foreach ($this->sections as $sectionType => $section) {
                $section->createRaw();
                $this->raw .= $section->getRaw();
            }
        }
    }

    protected function parsingRawPre() : void
    {
        $this->dbTransBegin();
    }

    protected function parsingRawInterrupt(string &$property) : bool
    {
        if ($property === static::SECTIONS_REACHED) {
// parsing sections
            foreach ($this->sections as $sectionType => $section) {
                $this->sections[$sectionType]->parseRaw($this->raw, $this->fieldOffset);

                if ($this->sections[$sectionType]->isParsingError()) {
                    $this->parsingError = true;
                    return false;
                }
            }
        }

        return true;
    }

    protected function parsingRawPost() : void
    {
        if ($this->dbInTransaction())  {
            if ($this->isParsingError()) {
                $this->dbTransRollback();
            } else {
                $this->save();
                $this->dbTransCommit();
            }
        }
    }

    public function save() : void {

    }

    public function saveRawForLaterPreparing() : void {

    }
}