<?php
/**
 * Base class for classes of messages between nodes
 */
abstract class aMessage extends aFieldSet implements constMessageParsingResult
{
    protected static $dbgLvl = Logger::DBG_MESS;  /* overrided */

    /** @var int  */
    protected static $id;  /* override me */

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
    protected $outgoingString = null;
    public function setOutgoingString(array $val) : self {$this->outgoingString = $val; return $this;}

    /** @var string  */
    protected $signedData = null;
    public function setSignedData(string $val) : self {$this->signedData = $val; return $this;}
    public function getSignedData() : string {return $this->signedData;}

    /** @var int  */
    protected $declaredLen = null;
    public function getDeclaredLen() : ?int {return $this->declaredLen;}

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
        $this->fieldOffset = MessageFieldClassEnum::getLength(MessageFieldClassEnum::TYPE);
        $this->name = MessageClassEnum::getItem(static::$id);
    }

    public static function create(aBase $parent, array $outData = []) : self
    {
        $me = new static($parent);

        if ($parent instanceof SocketLegate) {
// if parent object is "SocketLegate" - this is incoming message,
            $me->dbg(MessageClassEnum::getItem(static::$id) .  ' detected');
            $parent->setInMessage($me);
        } else {
// else - outgoing message (usually used aLocator-object as parent)
            $me->dbg(MessageClassEnum::getItem(static::$id) .  ' created');
            $me->setOutgoingString($outData);
        }

        return $me;
    }

    public static function debugForBadMessageType(aBase $parent, string $errMessage)
    {
// TODO remove this method after debug
        $me = new static($parent);
        $me->dbg($errMessage);
        unset($me);
    }

    /**
     * @param Socket $parent
     * @param int $id
     * @return aMessage|null
     * @throws Exception
     */
    public static function spawn(aBase $parent, int $id) : self
    {
        /** @var aMessage $className */

        if ($className = MessageClassEnum::getClassName($id)) {
            return $className::create($parent);
        }

        throw new Exception("Bad code - unknown message class for ID " . $id);
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
            $this->dbg("BAD DATA length $this->rawStringLen more than maximum $this->maxLen for $this->name");
            $legate->setBadData();
            return self::MESSAGE_PARSED;
        }

// check message len for declared len
        if ($this->declaredLen !== null && $this->rawStringLen > $this->declaredLen) {
            $this->dbg("BAD DATA length $this->rawStringLen more than declared length " . $this->declaredLen . " for $this->name (1)");
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
        $typeField = TypeMessageField::pack($this,static::$id);
        $messageStringLength = strlen($typeField) + aMessageField::getStatLength(MessageFieldClassEnum::LENGTH) + strlen($body);
        $lenField = LengthMessageField::pack($this,$messageStringLength);

        return $typeField . $lenField . $body;
    }

    /**
     * @return string
     */
    public function createMessageString() : string
    {
        return $this->compositeMessage('');
    }
}