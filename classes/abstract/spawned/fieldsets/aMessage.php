<?php
/**
 * Base classenum for classes of messages between nodes
 */
abstract class aMessage extends aFieldSet
{
    public const MESSAGE_PARSED = true;
    public const MESSAGE_NOT_PARSED = false;

    protected static $dbgLvl = Logger::DBG_MESSAGE;  /* overrided */

    /** @var string  */
    protected static $enumClass = 'MessageClassEnum'; /* overrided */

    /** @var string  */
    protected $fieldClass = 'aMessageField'; /* overrided */

    /** @var int  */
    protected $maxLen = MessageFieldClassEnum::BASE_MAX_LEN;   /* override me */
    public function getMaxLen() : int {return $this->maxLen;}

    /** @var int  */
    protected $fieldPointer = MessageFieldClassEnum::LENGTH;  /* overrided */    // first fieldId prepared inside Message-object (field 'Message Length')

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(      /* overrided */
        MessageFieldClassEnum::TYPE =>      'id',              // must be always first field in message
        MessageFieldClassEnum::LENGTH =>    'declaredLen',     // must be always second field in message
    );

    /** @var int  */
    protected $declaredLen = null;
    public function getDeclaredLen() : ?int {return $this->declaredLen;}

    /** @var int  */
    protected $incomingMessageTime = null;
    public function setIncomingMessageTime(int $val) : self {$this->incomingMessageTime = $val; return $this;}
    public function getIncomingMessageTime() : int {return $this->incomingMessageTime;}

    /** @var string  */
    protected $signedData = null;
    public function setSignedData(string $val) : self {$this->signedData = $val; return $this;}
    public function getSignedData() : string {return $this->signedData;}

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

    public static function create(aBase $parent) : self
    {
        $me = new static($parent);

        $me
            ->setIdFromEnum()
            ->setFieldOffset(MessageFieldClassEnum::getLength(MessageFieldClassEnum::TYPE));

        if ($parent instanceof SocketLegate) {
// if parent object is "SocketLegate" - this is incoming message,
            $me->setIsIncoming(true);
            $parent->setInMessage($me);
            $me->dbg($me->getName() .  ' detected');
        } else {
// else - outgoing message (usually used aLocator-object as parent)
            $me->setIsIncoming(false);
            $me->dbg($me->getName() .  " created");
        }

        return $me;
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

        $this->raw .= $packet;
        $this->rawLength = strlen($this->raw);

        if ($this->declaredLen) {
            $legate->setReadBufferSize($this->declaredLen - $this->rawLength + 1);
        }

// check message len for maximum len
        if ($this->maxLen && $this->rawLength > $this->maxLen) {
            $this->dbg("BAD DATA length $this->rawLength more than maximum $this->maxLen for " . $this->getName());
            $legate->setBadData();
            return self::MESSAGE_PARSED;
        }

// check message len for declared len
        if ($this->declaredLen !== null && $this->rawLength > $this->declaredLen) {
            $this->dbg("BAD DATA length $this->rawLength more than declared length $this->declaredLen for " . $this->getName() ." (1)");
            $legate->setBadData();
            return self::MESSAGE_PARSED;
        }

// parse raw string and prepare formats
        $this->parseRawString();

// check unpack maxLength or maxValue or fixLength error
        if ($this->field !== null && $this->field->getLength() === null && $this->field->getValue() === null) {
            $this->dbg("BAD DATA (see prev string))");
            $legate->setBadData();
            return self::MESSAGE_PARSED;
        }

        if ($legate->isBadData() || $legate->getResponseString() !== null) {
            return self::MESSAGE_PARSED;
        }

        if ($this->declaredLen === null || $this->rawLength < $this->declaredLen) {
            return self::MESSAGE_NOT_PARSED;
        }

        return $this->incomingMessageHandler();
    }

    protected function getLengthForLast() : int
    {
        return $this->declaredLen - $this->fieldOffset;
    }

    protected function compositeRaw() : string
    {
        $rawType = TypeMessageField::pack($this,$this->id);
        $fullLength = strlen($rawType) + aMessageField::getStatLength(MessageFieldClassEnum::LENGTH) + strlen($this->raw);
        $rawLength = LengthMessageField::pack($this,$fullLength);

        $this->raw = $rawType . $rawLength . $this->raw;
        $this->rawLength = strlen($this->raw);

        $this->dbg(get_class($this) . " raw created ($this->rawLength bytes):\n" . bin2hex($this->raw) . "\n");

        return $this->raw;
    }

    /**
     * @return string
     */
    public function createRaw() : string
    {
        $this->raw = '';
        return $this->compositeRaw();
    }
}