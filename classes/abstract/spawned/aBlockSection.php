<?php


abstract class aBlockSection extends aSpawnedFromEnum
{
    protected static $dbgLvl = Logger::DBG_BLOCK_SECTION;

    /** @var string  */
    protected static $enumClass = 'BlockSectionClassEnum'; /* overrided */

    protected $transactions = array();

    /** @var int  */
    protected $transactionsCountFormatId = null;

    /** @var int  */
    protected $transactionLengthFormatId = null;

    /** @var int  */
    protected $maxTransactionsCount = 0;

    public static function create(Block $parent) : self
    {
        $me = new static($parent);

        $me
            ->setIdFromEnum()
            ->setProps();

        $parent->dbg($me->getName() .  ' created');

        return $me;
    }

    public static function spawn(Block $parent, $id) : self
    {
        if (static::$enumClass === null) {
            throw new Exception("Bad code - not defined enumClass");
        }

        /** @var aClassEnum $enumClass */
        $enumClass = static::$enumClass;

        /** @var self $className */
        if ($className = $enumClass::getClassName($id)) {
            return $className::create($parent);
        }

        throw new Exception("Bad code - cannot spawn class from $enumClass for ID " . $id);
    }

    public function setProps() : self
    {
        $this->transactionLengthFormatId = BlockSectionClassEnum::getTransactionLengthFormatId($this->id);
        $this->transactionsCountFormatId = BlockSectionClassEnum::getTransactionCountFormatId($this->id);
        $this->maxTransactionsCount = FieldFormatClassEnum::getMaxValue($this->transactionsCountFormatId);

        return $this;
    }

    public function addTransaction(aTransaction $transaction) : self
    {
// check count of transaction for maximum
        if (count($this->transactions) === $this->maxTransactionsCount) {
// TODO добавить реакцию на неправильное количество транзакций в секции блока с прекращением его обработки
            return  $this;
        }

        $transaction->createRaw();
        $transactionHash = $transaction->getHash();
// TODO добавить проверку уникальности хэша транзакции для определенных типов нод
// TODO добавить проверку правильности сигнатуры транзакции для определенных типов нод

        $this->transactions[] = $transaction;

        return $this;
    }

    public function createRaw() : string
    {
        $this->raw = '';

        $this->rawLength = strlen($this->raw);

        $this->dbg($this->getName() . " raw created ($this->rawLength bytes):\n" . bin2hex($this->raw) . "\n");
        return $this->raw;
    }
}