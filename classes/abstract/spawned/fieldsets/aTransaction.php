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
    protected const AUTHOR_FIELD = 'authorAddress';
    protected const SIGNATURE_FIELD = 'signature';

    protected static $dbgLvl = Logger::DBG_TRANSACT;

    /** @var string  */
    protected static $enumClass = 'TransactionClassEnum'; /* overrided */

    /** @var string  */
    protected $fieldClass = 'aTransactionField'; /* overrided */

    /** @var bool  */
    protected static $isAuthor = true; /* can be overrided */
    public function isAuthor() : bool {return static::$isAuthor;}

    /* 'property' => '[fieldType, objectMethod or false]' or 'formatType' */
    protected static $fieldSet = array(      /* overrided */
        'type' => [TransactionFieldClassEnum::TYPE, false],              // must be always first field in message
        self::AUTHOR_FIELD => [TransactionFieldClassEnum::AUTHOR, 'getAddressBin'],
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
        self::SIGNATURE_FIELD => [TransactionFieldClassEnum::SIGN, false],
    );

    /** @var int  */
    protected $nonce = 0;
    public function getNonce() : int {return $this->nonce;}

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
            ->setFieldOffset(TransactionFieldClassEnum::getLength(TransactionFieldClassEnum::TYPE))
            ->removeAuthorAndSignIfNeed()
        ;

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

    public function removeAuthorAndSignIfNeed() : self
    {
        if (!static::$isAuthor) {
            unset($this->fields[self::AUTHOR_FIELD]);
            unset($this->fields[self::SIGNATURE_FIELD]);
        }

        return $this;
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
        $rawType = TypeTransactionField::pack($this, $this->type);

        if (static::$isAuthor) {
            if ($this->authorAddress === null) {
                throw new Exception($this->getName() . " Bad code - author address cannot be null");
            }

            if (!($this->authorAddress instanceof Address)) {
                throw new Exception($this->getName() . " Bad code - author address must be insatance of Address");
            }

            if (!$this->authorAddress->isFull()) {
                throw new Exception($this->getName() . " Bad code - author address must be full for sign transaction");
            }

            $rawAuthor = AuthorTransactionField::pack($this, $this->authorAddress->getAddressBin());
            $this->raw = $rawType . $rawAuthor . $this->raw;
        } else {
            $this->raw = $rawType . $this->raw;
        }

        $isUnique = false;
        $savedUniqueTransactionNonces = array();
        $i = 0;

        while (!$isUnique) {
            $rawNonce = NonceTransactionField::pack($this, $this->nonce);
            $raw = $this->raw . $rawNonce;
            $this->hash = $this->calcHash($raw);

            $savedUniqueTransactionNonces[$i] = UniqueTransactionNonceByHashDbRow::create($this, $this->hash);

            if (($nonce = $savedUniqueTransactionNonces[$i]->getNonce()) === null) {
                $isUnique = true;
            } else {
                $i++;
                $this->nonce = $nonce + 1;
            }
        }

        $this->raw = $raw;

        if (static::$isAuthor) {
            $this->signature = $this->getAuthorAddress()->signBin($this->raw);

            $rawSignature = SignTransactionField::pack($this, $this->signature);
            $this->raw .= $rawSignature;
        }

        $this->rawLength = strlen($this->raw);

        if ($this->rawLength > TransactionClassEnum::getMaxTransactionLength($this->type)) {
            throw new Exception($this->getName() . " Bad code - raw transaction length $this->rawLength more than maximal " . TransactionClassEnum::getMaxTransactionLength($this->type));
        }

        foreach ($savedUniqueTransactionNonces as $savedUniqueTransactionNonce) {
            $savedUniqueTransactionNonce
                ->setNonce($this->nonce)
                ->save();
        }

        $this->dbg($this->getName() . " raw created ($this->rawLength bytes):\n" . bin2hex($this->raw) . "\n");
    }

    public function setHash() : void
    {
        $this->hash = $this->calcHash($this->signedData);
    }

    public function calcHash(string &$data) : string
    {
        return hash(self::HASH_ALGO, $data, true);
    }

    public function saveAsPreparedTransaction() : bool
    {
        throw new Exception($this->getName() . " Bad code - not defined checkAndSave()");
    }

    public function saveAsNewTransaction() : self
    {
        if (static::$isAuthor) {
            NewAuthorTransactionByHashDbRow::create($this, $this->getHash())
                ->setTransaction($this)
                ->save()
            ;
        } else {
            NewSignerTransactionByHashDbRow::create($this, $this->getHash())
                ->setTransaction($this)
                ->save()
            ;
        }

        return $this;
    }
}