<?php
/**
 * Base classenum for blockchain transactions
 *
 * "Address-to-Chain without amount without data" (type C)
 * unfreeze all
 * undelegate all from all nodes
 */

abstract class aTransaction extends aFieldSet
{
    use tTransactionConstructor;
    public const HASH_ALGO = 'md4';
    public const HASH_BIN_LEN = 16;

    protected const DATA_PROPERTY = 'rawData';

    protected static $dbgLvl = Logger::DBG_TRANSACT;

    /** @var string  */
    protected static $enumClass = 'TransactionClassEnum'; /* overrided */

    /** @var string  */
    protected $fieldClass = 'aTransactionField'; /* overrided */

    /** @var int  */
    protected $fieldPointer = TransactionFieldClassEnum::AUTHOR;  /* overrided */    // first fieldId prepared inside ttransaction-object (field 'Author')

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(      /* overrided */
        TransactionFieldClassEnum::TYPE =>      'id',              // must be always first field in message
        TransactionFieldClassEnum::AUTHOR =>    'authorAddrBin',
    );

    /** @var string  */
    protected $authorAddrBin = null;
    public function getAuthorAddrBin() : string {return $this->authorAddrBin;}

    /** @var Address  */
    protected $authorAddress = null;
    public function setAuthorAddress(Address $val) : self {$this->authorAddress = $val; $this->authorAddrBin = $val->getAddressBin(); return $this;}
    public function getAuthorAddress() : Address {return $this->authorAddress;}

    protected static $fieldLastSet = array(  /* overrided */
        TransactionFieldClassEnum::SIGN =>      'signature',
        TransactionFieldClassEnum::HASH =>      'hash',
    );

    /** @var string  */
    protected $signature = null;
    public function getSignature() : string {return $this->signature;}

    /** @var string  */
    protected $hash = null;
    public function getHash() : string {return $this->hash;}

    /** @var string  */
    protected $internalHash = null;
    public function getInternalHash() : string {return $this->internalHash;}

    /** @var aTransactionData  */
    protected $data = null;
    public function getData() : aTransactionData {return $this->data;}

    /** @var int  */
    protected $dataClassId = null; /* override me */

    /** @var string  */
    protected $rawData = null;

    /** @var string  */
    protected $signedData = '';
    public function getSignedData() : string {return $this->signedData;}

    /** @var bool  */
    protected $isIncoming = null;
    public function setIsIncoming(bool $val) : self {$this->isIncoming = $val; return $this;}
    public function isIncoming() : bool {return $this->isIncoming;}

    public function getMessage() : aTransactionMessage
    {
        if ($this->getParent() instanceof aTransactionMessage) {
            return $this->getParent();
        }

        throw new Exception("Bad code - message-object can be used only in incoming transaction");
    }

    public static function create(aBase $parent) : self
    {
        $me = new static($parent);

        $me
            ->setIdFromEnum()
            ->setFieldOffset(TransactionFieldClassEnum::getLength(TransactionFieldClassEnum::TYPE))
            ->setData();

        if ($parent instanceof aTransactionMessage) {
// if parent object is "aTransactionMessage" - this is incoming message,
            $me->setIsIncoming(true);
            $parent->setTransaction($me);
            $me->dbg($me->getName() .  ' detected');
        } else {
            $me->setIsIncoming(false);
            $me->dbg($me->getName() .  " created");
        }

        return $me;
    }

    public function setData() : self
    {
        if ($this->dataClassId !== null) {
            $this->data = aTransactionData::spawn($this, $this->dataClassId);
        }

        return $this;
    }

    protected function compositeRaw() : string
    {
        $rawType = TypeTransactionField::pack($this, $this->id);
        $rawAuthor = AuthorTransactionField::pack($this, $this->authorAddrBin);

        $this->raw = $rawType . $rawAuthor . $this->raw;
        $this->signedData = $rawType . $rawAuthor . $this->signedData;

        if ($this->getAuthorAddress()->isFull()) {
            $this->signature = $this->getAuthorAddress()->signBin($this->signedData);
            $this->calcHash();

            $this->raw .= $this->signature . $this->hash;
        }

        $this->rawLength = strlen($this->raw);
        $this->calcInternalHash();

        $this->dbg($this->getName() . " raw created ($this->rawLength bytes):\n" . bin2hex($this->raw) . "\n");

        return $this->raw;
    }

    protected function calcHash()
    {
        $this->hash = hash(self::HASH_ALGO, $this->signature, true);
    }

    protected function calcInternalHash()
    {
        $this->internalHash = hash(self::HASH_ALGO, $this->signedData, true);
    }

    protected function createRawData() : void
    {
        if ($this->data === null) {
            throw new Exception($this->getName() . " Bad code - dataClassId must be defined (for ID $this->id)");
        }

        $this->rawData = $this->data->createRaw();

        if ($this->rawData === null) {
            throw new Exception($this->getName() . " Bad code - all data fields must be filled (for ID $this->id)");
        }
    }

    protected function postPrepareField(int $fieldId, string $property) : void /* overrided */
    {
        if ($property !== self::DATA_PROPERTY) return;

        if ($this->data === null) {
            throw new Exception($this->getName() . " Bad code - dataClassId must be defined (for ID $this->id)");
        }

        $this->data->setRaw($this->raw);
        $this->data->parseRawString();
    }
}