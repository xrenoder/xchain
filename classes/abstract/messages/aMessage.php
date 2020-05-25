<?php
/**
 * Base class for classes of messages between nodes
 */
abstract class aMessage extends aBase implements constMessageParsingResult
{
    protected static $dbgLvl = Logger::DBG_MESS;  /* overrided */

    /** @var int  */
    protected static $id;  /* override me */

    /** @var int  */
    protected $maxLen = null;   /* override me */
    public function getMaxLen() : int {return $this->maxLen;}

    /**
     * override me
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(
        MessageFieldClassEnum::TYPE =>      '',                // must be always first field in message
        MessageFieldClassEnum::LENGTH =>    'declaredLen',     // must be always second field in message
    );

    protected $fields = array();

    /** @var string */
    protected $name;
    public function getName() : string {return $this->name;}

    /** @var string */
    private $incomingString;

    /** @var int  */
    protected $incomingStringLen = null;
    public function getIncomingStringLen() : ?int {return $this->incomingMessageTime;}

    /** @var int  */
    protected $incomingMessageTime = null;
    public function setIncomingMessageTime(int $val) : self {$this->incomingMessageTime = $val; return $this;}
    public function getIncomingMessageTime() : int {return $this->incomingMessageTime;}

    /** @var array  */
    protected $outgoingString = null;
    public function setOutgoingString(array $val) : self {$this->outgoingString = $val; return $this;}

    /** @var int  */
    private $fieldPointer = MessageFieldClassEnum::LENGTH;      // first fieldId prepared inside message-object (field 'Message Length')

    /** @var aMessageField  */
    private $fieldObject = null;

    /** @var int  */
    private $fieldOffset = null;

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
    public static function spawn(aBase $parent, int $id) : ?self
    {
        /** @var aMessage $className */

        if ($className = MessageClassEnum::getClassName($id)) {
            return $className::create($parent);
        }

        return null;
    }

    /**
     * @param string $packet
     * @return bool
     */
    public function addPacket(string $packet) : bool
    {
        $legate = $this->getLegate();

// if server is busy - not check incoming fields, quick answer 'busy' and disconnect
        if ($legate->isServerBusy()) {
            $legate->setCloseAfterSend();
            $legate->createResponseString(BusyResMessage::create($this->getLocator()));
            return self::MESSAGE_PARSED;
        }

        $this->incomingString .= $packet;
        $this->incomingStringLen = strlen($this->incomingString);

        if ($this->declaredLen) {
            $legate->setReadBufferSize($this->declaredLen - $this->incomingStringLen + 1);
        }


// check message len for maximum len
        if ($this->maxLen && $this->incomingStringLen > $this->maxLen) {
            $this->dbg("BAD DATA length $this->incomingStringLen more than maximum $this->maxLen for $this->name");
            $legate->setBadData();
            return self::MESSAGE_PARSED;
        }

// check message len for declared len
        if ($this->declaredLen !== null && $this->incomingStringLen > $this->declaredLen) {
            $this->dbg("BAD DATA length $this->incomingStringLen more than declared length " . $this->declaredLen . "for $this->name (1)");
            $legate->setBadData();
            return self::MESSAGE_PARSED;
        }

// prepare fields
        foreach ($this->fields as $fieldId => $property) {
            if ($this->fieldPointer > $fieldId) {
                continue;
            }

            if (!$this->prepareField($fieldId, $property)) {
// if field cannot be prepared - break  (not 'return false'), may be all fields was prepared
                break;
            }
        }

        if ($legate->isBadData() || $legate->getResponseString() !== null) {
            return self::MESSAGE_PARSED;
        }

        if ($this->declaredLen === null || $this->incomingStringLen < $this->declaredLen) {
            return self::MESSAGE_NOT_PARSED;
        }

        return $this->incomingMessageHandler();
    }

    protected function prepareField(int $fieldId, string $property) : bool
    {
        if ($this->$property !== null) return true;

        if ($this->fieldObject === null) {
            $this->fieldObject = aMessageField::spawn($this, $fieldId, $this->fieldOffset);

            if ($this->fieldObject->isLast()) {
                $this->fieldObject
                    ->setLength($this->declaredLen - $this->fieldOffset)
                    ->setPoint();
            }
        }

        if ($this->incomingStringLen >= $this->fieldObject->getPoint()) {
            [$length, $this->$property] = $this->fieldObject->unpackField($this->incomingString);

            if ($this->$property === null) {
                $this->dbg("Prepare field " . $this->fieldObject->getName() . ": field length = " . $length);
                $this->fieldObject->setLength($length);
                $this->fieldObject->setPoint();

                return $this->prepareField($fieldId, $property);
            }

            $this->dbg("Prepare field " . $this->fieldObject->getName() . ": $property = " . $this->$property);
            $result = $this->fieldObject->check();

            $this->fieldObject = null;
            $this->fieldOffset += $length;
            $this->fieldPointer = $fieldId + 1;

            return $result;
        }

        return false;
    }

    protected function compileMessage(string $body) : string
    {
        $typeField = TypeMessageField::packField(static::$id);
        $messageStringLength = strlen($typeField) + LengthMessageField::getLength() + strlen($body);
        $lenField = LengthMessageField::packField($messageStringLength);

        return $typeField . $lenField . $body;
    }

    /**
     * @return string
     */
    public function createMessageString() : string
    {
        return $this->compileMessage('');
    }
}