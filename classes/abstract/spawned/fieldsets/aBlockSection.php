<?php


abstract class aBlockSection extends aSpawnedFromEnum
{
    protected static $dbgLvl = Logger::DBG_BLOCK_SECTION;

    /** @var string  */
    protected static $enumClass = 'BlockSectionClassEnum'; /* overrided */

    /** @var aTransaction[]  */
    protected $transactions = array();

    /** @var int  */
    protected $transactionsCount = null;

    /** @var int  */
    protected $transactionsCountFormatType = null;

    /** @var int  */
    protected $transactionRawFormatType = null;

    /** @var int  */
    protected $maxTransactionsCount = 0;

    /** @var string */
    protected $raw = null;
    public function &getRaw() : ?string {if ($this->raw === null) {$this->createRaw();} return $this->raw;}

    public function getBlock() : Block {return $this->getParent();}

    public static function create(Block $parent) : self
    {
        $me = new static($parent);

        $me
            ->setTypeFromEnum()
            ->setProps();

        $parent->dbg($me->getName() .  ' created');

        return $me;
    }

    public static function spawn(Block $parent, $type) : self
    {
        if (static::$enumClass === null) {
            throw new Exception("Bad code - not defined enumClass");
        }

        /** @var aClassEnum $enumClass */
        $enumClass = static::$enumClass;

        /** @var self $className */
        if ($className = $enumClass::getClassName($type)) {
            return $className::create($parent);
        }

        throw new Exception("Bad code - cannot spawn class from $enumClass for type " . $type);
    }

    public function setProps() : self
    {
        $this->transactionRawFormatType = BlockSectionClassEnum::getTransactionRawFormatType($this->type);
        $this->transactionsCountFormatType = BlockSectionClassEnum::getTransactionCountFormatType($this->type);
        $this->maxTransactionsCount = FieldFormatClassEnum::getMaxValue($this->transactionsCountFormatType);

        return $this;
    }

    public function addTransaction(aTransaction $transaction) : self
    {
// check count of transaction for maximum
        if (count($this->transactions) === $this->maxTransactionsCount) {
// TODO добавить реакцию на неправильное количество транзакций в секции блока с прекращением его обработки
            return  $this;
        }

        $transactionHash = $transaction->getHash();
// TODO добавить проверку уникальности хэша транзакции для определенных типов нод
// TODO добавить проверку правильности сигнатуры транзакции для определенных типов нод

        $this->transactions[] = $transaction;

        return $this;
    }

    public function parseRaw(string &$raw, int &$offset) : void
    {
        $block = $this->getBlock();

        $transactionsCount = $this->simpleUnpack($this->transactionsCountFormatType, $raw, $offset);
        $signedData = $this->getUnpackedRaw();
        $offset += $this->getUnpackedLength();

        for($i = 0; $i < $transactionsCount; $i++) {
            $transactionRaw = $this->simpleUnpack($this->transactionRawFormatType, $raw, $offset);
            $signedData .= $this->getUnpackedRaw();
            $offset += $this->getUnpackedLength();

            $transactionType = aTransaction::parseType($this, $transactionRaw);

            if ($transactionType === null) {
                $this->err($this->getName() . " parsing error: transaction-object cannot be created from type $transactionType");
                $this->parsingError = true;
                return;
            }

            if (TransactionClassEnum::getBlockSectionType($transactionType) !== $this->getType()) {
                $this->err($this->getName() . " parsing error: transaction type $transactionType cannot be placed in block section type " . $this->getType());
                $this->parsingError = true;
                return;
            }

            $this->transactions[$i] =
                aTransaction::spawn($this->getBlock(), $transactionType)
                ->setRaw($transactionRaw);

            $this->transactions[$i]->parseRaw();

            if ($this->transactions[$i]->isParsingError()) {
                $this->parsingError = true;
                return;
            }

            if ($block->dbInTransaction()) {
                if (!$this->transactions[$i]->save()) {
                    $this->parsingError = true;
                    return;
                }
            }
        }

        $block->addSignedData($signedData);
    }

    public function createRaw()
    {
        $transactionsCount = count($this->transactions);
        $this->raw = $this->simplePack($this->transactionsCountFormatType, $transactionsCount);

        foreach($this->transactions as $transaction) {
            $this->raw .= $this->simplePack($this->transactionRawFormatType, $transaction->getRaw());
        }

        $this->rawLength = strlen($this->raw);

        $this->dbg($this->getName() . " raw created ($this->rawLength bytes):\n" . bin2hex($this->raw) . "\n");
        return $this;
    }
}