<?php


class Block extends aBase
{
    protected static $dbgLvl = Logger::DBG_BLOCK;

    /** @var int  */
    private $chainNumber = 0;
    public function setChainNumber(int $val) : self {$this->chainNumber = $val; return $this;}
    public function getChainNumber() : int {return $this->chainNumber;}

    /** @var ?Block  */
    private $prevBlock = null;
    public function setPrevBlock(Block $val) : self {$this->prevBlock = $val; $this->blockNumber = $val->getBlockNumber() + 1; $this->prevSignature = $val->getSignature(); return $this;}
    public function getPrevBlock() : ?Block {return $this->prevBlock;}

    /** @var int  */
    private $blockNumber = 0;
    public function getBlockNumber() : int {return $this->blockNumber;}

    /** @var string  */
    private $prevSignature = '';
    public function getPrevSignature() : string {return $this->prevSignature;}

    /** @var int  */
    private $blockTime = null;
    public function setBlockTime(int $val) : self {$this->blockTime = $val; return $this;}
    public function getBlockTime() : int {return $this->blockTime;}

    /** @var Address  */
    private $signerAddress = null;
    public function setSignerAddress(Address $val) : self {$this->signerAddress = $val; return $this;}
    public function getSignerAddress() : Address {return $this->signerAddress;}

    /** @var string  */
    private $signedData = '';
    public function getSignedData() : string {return $this->signedData;}

    /** @var string  */
    private $signature = null;
    public function getSignature() : string {return $this->signature;}

    /** @var   */
    private $sections = array();

    public static function createNew(aBase $parent, Address $signerAddress, ?Block $prevBlock, int $chainNumber) : self
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
            ->setBlockTime(time())
            ->setSignerAddress($signerAddress)
        ;

        return $me;
    }

    public function addTransaction(aTransaction $transaction) : self
    {
        $transactionId = $transaction->getId();

        $sectionId = TransactionClassEnum::getBlockSectionId($transactionId);

        if (!isset($this->sections[$sectionId])) {
            $this->sections[$sectionId] = aBlockSection::spawn($this, $sectionId);
        }

        /** @var aBlockSection $section */
        $section = $this->sections[$sectionId];



        $section->addTransaction($transaction);

        return $this;
    }
}