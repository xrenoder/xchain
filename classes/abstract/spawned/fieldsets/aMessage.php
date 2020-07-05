<?php
/**
 * Base classenum for classes of messages between nodes
 */
abstract class aMessage extends aFieldSet
{
    use tMessageConstructor;

    public const MESSAGE_PARSED = true;
    public const MESSAGE_NOT_PARSED = false;

    protected static $dbgLvl = Logger::DBG_MESSAGE;  /* overrided */

    /** @var string  */
    protected static $enumClass = 'MessageClassEnum'; /* overrided */

    /** @var string  */
    protected $fieldClass = 'aMessageField'; /* overrided */

    /** @var int  */
    protected $maxLen = null;
    public function getMaxLen() : int {return $this->maxLen;}

    /** @var int  */
    protected $fieldPointer = 1;  /* overrided */    // first fieldId prepared inside Message-object (field 'Message Length')

    /* 'property' => [fieldType, isObject] */
    protected static $fieldSet = array(      /* overrided */
        'type' => [MessageFieldClassEnum::TYPE, false],              // must be always first field in message
        'declaredLen' => [MessageFieldClassEnum::LENGTH, false],     // must be always second field in message
    );

    /** @var int  */
    protected $declaredLen = null;
    public function getDeclaredLen() : ?int {return $this->declaredLen;}

    /** @var int  */
    protected $dataClass = null; /* override me */
    public function getDataClass() : int {return $this->dataClass;}

    /** @var aMessageData  */
    protected $data = null;

    /** @var int  */
    protected $incomingMessageTime = null;
    public function setIncomingMessageTime(int $val) : self {$this->incomingMessageTime = $val; return $this;}
    public function getIncomingMessageTime() : int {return $this->incomingMessageTime;}

    /** @var string  */
    protected $signedData = '';
    public function setSignedData(string &$val) : self {$this->signedData = $val; return $this;}
    public function addSignedData(string &$val) : self {$this->signedData .= $val; return $this;}
    public function addBeforeSignedData(string &$val) : self {$this->signedData = $val . $this->signedData; return $this;}
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

        throw new Exception($this->getName() . " Bad code - legate cannot be used in outgoing message");
    }

    public static function parseType(aBase $parent, string &$raw) : ?int
    {
        $field = TypeMessageField::create($parent);
        $result = $field->unpack($raw);
        unset($field);

        if (!MessageClassEnum::isSetItem($result)) {
            return null;
        }

        return $result;
    }

    public static function create(aBase $parent) : self
    {
        $me = new static($parent);

        $me
            ->setTypeFromEnum()
            ->setMaxLen()
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

    public function setMaxLen() : aMessage
    {
        $this->maxLen = MessageFieldClassEnum::getBaseMaxLen();

        return $this;
    }

    public function getData() : aMessageData
    {
        if ($this->data === null) {
            if ($this->dataClass !== null) {
                throw new Exception($this->getName() . " Bad code - not defined dataClass");
            }

            $this->data = aMessageData::spawn($this, $this->dataClass);
        }

        return $this->data;
    }

    /**
     * @param string $packet
     * @return bool
     */
    public function addPacket(string &$packet) : bool
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
        $this->parseRaw();

// check bad data errors: maxLength or maxValue or fixLength error or impossible semantic
        if ($this->parsingError) {
            $this->dbg("BAD DATA (see prev string))");
            $legate->setBadData();
            return self::MESSAGE_PARSED;
        }

        if ($legate->getResponseString() !== null) {
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

    protected function compositeRaw() : void
    {
        $rawType = TypeMessageField::pack($this,$this->type);
        $fullLength = strlen($rawType) + aMessageField::getStaticLength(MessageFieldClassEnum::LENGTH) + strlen($this->raw);
        $rawLength = LengthMessageField::pack($this,$fullLength);

        $this->raw = $rawType . $rawLength . $this->raw;
        $this->rawLength = strlen($this->raw);

        $this->dbg(get_class($this) . " raw created ($this->rawLength bytes):\n" . bin2hex($this->raw) . "\n");
    }

    public function createRaw() : aFieldSet
    {
        $this->raw = '';
        $this->compositeRaw();

        return $this;
    }
}