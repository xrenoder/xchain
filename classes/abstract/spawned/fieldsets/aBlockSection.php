<?php


abstract class aBlockSection extends aSpawnedFromEnum
{
    protected static $dbgLvl = Logger::DBG_BLOCK_SECTION;

    /** @var string  */
    protected static $enumClass = 'BlockSectionClassEnum'; /* overrided */

    /** @var aTransaction[]  */
    protected $transactions = array();

    /** @var int  */
    protected $transactionsCountFormatType = null;

    /** @var int  */
    protected $transactionLengthFormatType = null;

    /** @var int  */
    protected $maxTransactionsCount = 0;

    /** @var string */
    protected $raw = null;
    public function getRaw() : ?string {return $this->raw;}

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
        $this->transactionLengthFormatType = BlockSectionClassEnum::getTransactionLengthFormatId($this->type);
        $this->transactionsCountFormatType = BlockSectionClassEnum::getTransactionCountFormatId($this->type);
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

    public function createRaw()
    {
        $transactionsCount = count($this->transactions);
        $this->raw = $this->simplePack($this->transactionsCountFormatType, $transactionsCount);

        foreach($this->transactions as $transaction) {
            $this->raw .= $this->simplePack($this->transactionLengthFormatType, $transaction->getRawLength()) . $transaction->getRaw();
        }

        $this->rawLength = strlen($this->raw);

        $this->dbg($this->getName() . " raw created ($this->rawLength bytes):\n" . bin2hex($this->raw) . "\n");
        return $this;
    }
}