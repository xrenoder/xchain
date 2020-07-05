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

    protected static $dbgLvl = Logger::DBG_TRANSACT;

    /** @var string  */
    protected static $enumClass = 'TransactionClassEnum'; /* overrided */

    /** @var string  */
    protected $fieldClass = 'aTransactionField'; /* overrided */

    /* 'property' => '[fieldType, isObject]' or 'formatType' */
    protected static $fieldSet = array(      /* overrided */
        'type' => [TransactionFieldClassEnum::TYPE, false],              // must be always first field in message
        'authorAddress' => [TransactionFieldClassEnum::AUTHOR, 'getAddressBin'],
    );

    /** @var Address  */
    protected $authorAddress = null;
    public function setAuthorAddress(Address $val) : self {$this->authorAddress = $val; return $this;}
    public function getAuthorAddress() : Address {return $this->authorAddress;}

    /** @var int  */
    protected $dataClass = null; /* override me */
    public function getDataClass() : int {return $this->dataClass;}

    /** @var aTransactionData  */
    protected $data = null;

    protected static $fieldLastSet = array(  /* overrided */
        'nonce' => [TransactionFieldClassEnum::NONCE, false],
        'signature' => [TransactionFieldClassEnum::SIGN, false],
    );

    /** @var int  */
    protected $nonce = 0; /* override me */
    public function getNonce() : ?int {return $this->nonce;}

    /** @var string  */
    protected $signedData = null;
    public function setSignedData(string &$val) : self {$this->signedData = $val; return $this;}
    public function addSignedData(string &$val) : self {$this->signedData .= $val; return $this;}
    public function &getSignedData() : string {return $this->signedData;}

    /** @var string  */
    protected $signature = null;
    public function getSignature() : string {return $this->signature;}

    /** @var string  */
    protected $hash = null;
    public function getHash() : string {return $this->hash;}

    /** @var string  */
    protected $internalHash = null;
    public function getInternalHash() : string {return $this->internalHash;}

    /** @var bool  */
    protected $isIncoming = null;
    public function setIsIncoming(bool $val) : self {$this->isIncoming = $val; return $this;}
    public function isIncoming() : bool {return $this->isIncoming;}

    public function getMessage() : TransactionMessage
    {
        if ($this->getParent() instanceof TransactionMessage) {
            return $this->getParent();
        }

        throw new Exception("Bad code - message-object can be used only in incoming transaction");
    }

    public static function parseType(aBase $parent, string &$raw) : ?int
    {
        $field = TypeTransactionField::create($parent);
        $result = $field->unpack($raw);
        unset($field);

        if (!TransactionClassEnum::isSetItem($result)) {
            return null;
        }

        return $result;
    }

    public static function create(aBase $parent) : self
    {
        $me = new static($parent);

        $me
            ->setTypeFromEnum()
            ->setFieldOffset(TransactionFieldClassEnum::getLength(TransactionFieldClassEnum::TYPE));

        if ($parent instanceof TransactionMessage) {
// if parent object is "aTransactionMessage" - this is incoming message,
            $me->setIsIncoming(true);
//            $parent->setTransaction($me);
            $me->dbg($me->getName() .  ' detected');
        } else {
            $me->setIsIncoming(false);
            $me->dbg($me->getName() .  " created");
        }

        return $me;
    }

    public function getData() : aTransactionData
    {
        if ($this->data === null) {
            if ($this->dataClass !== null) {
                throw new Exception($this->getName() . " Bad code - not defined dataClass");
            }

            $this->data = aTransactionData::spawn($this, $this->dataClass);
        }

        return $this->data;
    }

    protected function compositeRaw() : void
    {
        if (!$this->getAuthorAddress()->isFull()) {
            throw new Exception($this->getName() . " Bad code - address must be full for sign transaction");
        }

        $rawType = TypeTransactionField::pack($this, $this->type);
        $rawAuthor = AuthorTransactionField::pack($this, $this->authorAddress->getAddressBin());

        $this->raw = $rawType . $rawAuthor . $this->raw;
        $this->signedData = $rawType . $rawAuthor . $this->signedData;

        $rawNonce = NonceTransactionField::pack($this, $this->nonce);
        $signedData = $this->signedData . $rawNonce;
        $this->signature = $this->getAuthorAddress()->signBin($signedData);
        $this->setHash();

// TODO добавить проверку на уникальность хэша транзакции в блокчейне

        $this->signedData = $signedData;

        $rawSignature = SignTransactionField::pack($this, $this->signature);
        $this->raw .= $rawSignature;

        $this->rawLength = strlen($this->raw);

        if ($this->rawLength > TransactionClassEnum::getMaxTransactionLength($this->type)) {
            throw new Exception($this->getName() . " Bad code - raw transaction length $this->rawLength more than maximal " . TransactionClassEnum::getMaxTransactionLength($this->type));
        }

        $this->setHash();

        $this->dbg($this->getName() . " raw created ($this->rawLength bytes):\n" . bin2hex($this->raw) . "\n");
    }

    public function setHash() : self
    {
        $this->hash = $this->calcHash($this->signature);
        $this->internalHash = $this->calcHash($this->signedData);

        return $this;
    }

    public function calcHash(string $data) : string
    {
        return hash(self::HASH_ALGO, $data, true);
    }

    public function save() : bool
    {
        throw new Exception($this->getName() . " Bad code - not defined checkAndSave()");
    }
}