<?php
/**
 * Base classenum for classes of messages between nodes
 */
abstract class aMessage extends aFieldSet implements constMessageParsingResult
{
    protected static $dbgLvl = Logger::DBG_MESS;  /* overrided */

    /** @var string  */
    protected $enumClass = 'MessageClassEnum'; /* overrided */

    /** @var int  */
    protected $maxLen = MessageFieldClassEnum::BASE_MAX_LEN;   /* override me */
    public function getMaxLen() : int {return $this->maxLen;}

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(      /* overrided */
        MessageFieldClassEnum::TYPE =>      '',                // must be always first field in message
        MessageFieldClassEnum::LENGTH =>    'declaredLen',     // must be always second field in message
    );

    /** @var int  */
    protected $fieldPointer = MessageFieldClassEnum::LENGTH;  /* overrided */    // first fieldId prepared inside Message-object (field 'Message Length')

    /** @var int  */
    protected $incomingMessageTime = null;
    public function setIncomingMessageTime(int $val) : self {$this->incomingMessageTime = $val; return $this;}
    public function getIncomingMessageTime() : int {return $this->incomingMessageTime;}

    /** @var array  */
    protected $outData = null;
    public function setOutData(array $val) : self {$this->outData = $val; return $this;}

    /** @var string  */
    protected $signedData = null;
    public function setSignedData(string $val) : self {$this->signedData = $val; return $this;}
    public function getSignedData() : string {return $this->signedData;}

    /** @var int  */
    protected $declaredLen = null;
    public function getDeclaredLen() : ?int {return $this->declaredLen;}

    /** @var bool  */
    protected $isIncoming = null;
    public function setIsIncoming(bool $val) : self {$this->isIncoming = $val; return $this;}
    public function isIncoming() : bool {return $this->isIncoming;}

    abstract protected function incomingMessageHandler() : bool;

    public function getLegate() : SocketLegate
    {
        if ($this->getParent() instanceof SocketLegate) {
            return $this->getParent();
        }

        throw new Exception("Bad code - legate cannot be used in outgoing message");
    }

    protected function __construct(aBase $parent)
    {
        parent::__construct($parent);
        $this->fields = array_replace($this->fields, self::$fieldSet);
    }

    public static function create(aBase $parent, array $outData = []) : self
    {
        $me = new static($parent);

        /** @var aClassEnum $enumClass */
        $enumClass = $me->getEnumClass();

        if (($id = $enumClass::getIdByClassName(get_class($me))) === null) {
            throw new Exception("Bad code - unknown ID (not found or not exclusive) for classenum " . $me->getName());
        }

        $me
            ->setId($id)
            ->setFieldOffset(MessageFieldClassEnum::getLength(MessageFieldClassEnum::TYPE));

        if ($parent instanceof SocketLegate) {
// if parent object is "SocketLegate" - this is incoming message,
            $me->setIsIncoming(true);
            $parent->setInMessage($me);
            $me->dbg($me->getName() .  ' detected');
        } else {
// else - outgoing message (usually used aLocator-object as parent)
            $me->setIsIncoming(false);
            $me->setOutData($outData);
            $me->dbg($me->getName() .  " created");
        }

        return $me;
    }

    /**
     * @param Socket $parent
     * @param int $id
     * @return aMessage|null
     * @throws Exception
     */
    public static function spawn(aBase $parent, int $id, array $outData = []) : self
    {
        /** @var aMessage $className */

        if ($className = MessageClassEnum::getClassName($id)) {
            return $className::create($parent, $outData);
        }

        throw new Exception("Bad code - unknown message classenum for ID " . $id);
    }

    /**
     * @param string $packet
     * @return bool
     */
    public function addPacket(string $packet) : bool
    {
        $legate = $this->getLegate();

// if server is busy - not check incoming formats, quick answer 'busy' and disconnect
        if ($legate->isServerBusy()) {
            $legate->setCloseAfterSend();
            $legate->createResponseString(BusyResMessage::create($this->getLocator()));
            return self::MESSAGE_PARSED;
        }

        $this->rawString .= $packet;
        $this->rawStringLen = strlen($this->rawString);

        if ($this->declaredLen) {
            $legate->setReadBufferSize($this->declaredLen - $this->rawStringLen + 1);
        }

// check message len for maximum len
        if ($this->maxLen && $this->rawStringLen > $this->maxLen) {
            $this->dbg("BAD DATA length $this->rawStringLen more than maximum $this->maxLen for " . $this->getName());
            $legate->setBadData();
            return self::MESSAGE_PARSED;
        }

// check message len for declared len
        if ($this->declaredLen !== null && $this->rawStringLen > $this->declaredLen) {
            $this->dbg("BAD DATA length $this->rawStringLen more than declared length $this->declaredLen for " . $this->getName() ." (1)");
            $legate->setBadData();
            return self::MESSAGE_PARSED;
        }

// parse raw string and prepare formats
        $this->parseRawString();

        if ($legate->isBadData() || $legate->getResponseString() !== null) {
            return self::MESSAGE_PARSED;
        }

        if ($this->declaredLen === null || $this->rawStringLen < $this->declaredLen) {
            return self::MESSAGE_NOT_PARSED;
        }

        return $this->incomingMessageHandler();
    }

    protected function spawnField(int $fieldId) : aField
    {
        return aMessageField::spawn($this, $fieldId, $this->fieldOffset);
    }

    protected function getLengthForLast() : int
    {
        return $this->declaredLen - $this->fieldOffset;
    }

    protected function compositeMessage(string $body) : string
    {
        $typeField = TypeMessageField::pack($this,$this->id);
        $messageStringLength = strlen($typeField) + aMessageField::getStatLength(MessageFieldClassEnum::LENGTH) + strlen($body);
        $lenField = LengthMessageField::pack($this,$messageStringLength);

        $messageString = $typeField . $lenField . $body;

        $this->dbg(get_class($this) . " string created:\n" . bin2hex($messageString) . "\n");

        return $messageString;
    }

    /**
     * @return string
     */
    public function createMessageString() : string
    {
        return $this->compositeMessage('');
    }
}