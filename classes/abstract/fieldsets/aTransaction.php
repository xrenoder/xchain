<?php
/**
 * Base classenum for blockchain transactions
 *
 * "Address-to-Chain without amount without data"
 * unfreeze all
 * undelegate all from all nodes
 */

abstract class aTransaction extends aFieldSet
{
    protected static $dbgLvl = Logger::DBG_TRANSACT;

    /** @var string  */
    protected $enumClass = 'TransactionClassEnum'; /* overrided */

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
    public function setAuthorAddrBin(string $val) : self {$this->authorAddress = $val; return $this;}
    public function getAuthorAddrBin() : string {return $this->authorAddrBin;}

    /** @var Address  */
    protected $authorAddress = null;
    public function setAuthorAddress(Address $val) : self {$this->authorAddress = $val; return $this;}
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
    protected $signedData = null;
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

    protected function __construct(aBase $parent)
    {
        parent::__construct($parent);
//        $this->fields = array_diff_key($this->fields, static::$fieldLastSet);
        $this->fields = array_replace($this->fields, self::$fieldSet);
        $this->fields = array_replace($this->fields, static::$fieldLastSet);

        $this->dbg($this->getName() .  ' fields:');
        $this->dbg(var_export($this->fields, true));
    }

    public static function create(aBase $parent) : self
    {
        $me = new static($parent);

        $me
            ->setIdFromEnum()
            ->setFieldOffset(TransactionFieldClassEnum::getLength(TransactionFieldClassEnum::TYPE));

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

    public static function spawn(aBase $parent, int $id) : self
    {
        /** @var aTransaction $className */

        if ($className = TransactionClassEnum::getClassName($id)) {
            return $className::create($parent);
        }

        throw new Exception("Bad code - unknown transaction classenum for ID " . $id);
    }

    protected function spawnField(int $fieldId) : aField
    {
        return aTransactionField::spawn($this, $fieldId, $this->fieldOffset);
    }
}