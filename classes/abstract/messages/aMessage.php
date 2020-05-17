<?php
/**
 * Base class for classes of messages between nodes
 */
abstract class aMessage extends aBase
{
    protected static $dbgLvl = Logger::DBG_MESS;

    /** @var int  */
    protected static $id;  /* override me */

    /** @var int  */
    protected $maxLen = null;   /* override me */
    public function getMaxLen() : int {return $this->maxLen;}

    /**
     * If false - this message can be first sended or received after socket creation
     * @var bool
     */
    protected static $needAliveCheck = true; /** can be overrided or not */

    /**
     * override me
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(
        MessageFieldClassEnum::TYPE =>      '',                // must be always first field in message
        MessageFieldClassEnum::LENGTH =>    'declaredLen',     // must be always second field in message
    );

    protected $fields = null;

    public function getSocket() : Socket {return $this->getParent();}

    /** @var string */
    protected $name;
    public function setName() : self {$this->name = MessageClassEnum::getItem(static::$id); return $this;}
    public function getName() : string {return $this->name;}

    /** @var string */
    private $str;

    /** @var int  */
    protected $len = null;
    public function getLen() : ?int {return $this->len;}

    /** @var array  */
    protected $outData = null;
    public function setOutData(array $val) : self {$this->outData = $val; return $this;}

    /** @var int  */
    private $fieldPointer = MessageFieldClassEnum::LENGTH;      // first fieldId prepared inside message-object (field 'Message Length')

    /** @var aMessageField  */
    private $fieldObject = null;

    /** @var int  */
    private $fieldOffset = null;
    public function setFieldOffset() : self {$this->fieldOffset = MessageFieldClassEnum::getLength(MessageFieldClassEnum::TYPE); return $this;}

    /** @var int  */
    protected $declaredLen = null;
    public function getDeclaredLen() : ?int {return $this->declaredLen;}

    abstract public function createMessageString(string $data = null) : string;
    abstract protected function incomingMessageHandler() : bool;

    protected function __construct(aBase $parent)
    {
        aBase::__construct($parent);
        $this->fields = static::mergeFields();
        $this->dbg("\n" . MessageClassEnum::getItem(static::$id) . " fields:\n" . var_export($this->fields, true) . "\n");
    }

    /**
     * @param Socket $socket
     * @return aMessage|null
     */
    public static function create(Socket $socket, ?array $outData = null) : ?self
    {
        if ($outData === null && static::$needAliveCheck && !$socket->isAliveChecked()) {
            $socket->dbg(MessageClassEnum::getItem(static::$id) . ' cannot explored before Alive checking');
            return null;
        }

        if ($outData === null) {
            $socket->dbg(MessageClassEnum::getItem(static::$id) .  ' detected');
        } else {
            $socket->dbg(MessageClassEnum::getItem(static::$id) .  ' created');
        }

        $me = new static($socket);

        $me
            ->checkFieldsId()
            ->setName()
            ->setFieldOffset();

        if ($outData === null) {
            $socket->setInMessage($me);
        } else {
            $me->setOutData($outData);
        }

        return $me;
    }

    protected static function mergeFields()
    {
        $localFields = self::$fieldSet;

        $parent = get_parent_class();
        $method = __FUNCTION__;

        if ($parent && method_exists($parent, $method)) {
            $parentFields = $parent::$method();
        } else {
            $parentFields = array();
        }

        return array_replace($parentFields, $localFields);
    }

    public function checkFieldsId() : self
    {
        $prevId = -1;

        foreach ($this->fields as $fieldId => $property) {
            if ($fieldId <= $prevId) {
                throw new Exception("Bad fields set description - not serial, later field less or equal previous");
            }

            $prevId = $fieldId;
        }

        return $this;
    }

    /**
     * @param Socket $socket
     * @param int $id
     * @return aMessage|null
     * @throws Exception
     */
    public static function spawn(Socket $socket, int $id) : ?self
    {
        /** @var aMessage $className */

        if ($className = MessageClassEnum::getClassName($id)) {
            return $className::create($socket, null);
        }

        return null;
    }

    /**
     * @param Socket $socket
     * @param string $packet
     * @return bool
     * @throws Exception
     */
    public static function parser(Socket $socket, string $packet) : bool
    {
        if (!$socket->getInMessage()) {
            $messageType = FieldFormatEnum::unpack($packet,MessageFieldClassEnum::getFormat(MessageFieldClassEnum::TYPE), 0)[1];

            if (!($message = self::spawn($socket, $messageType))) {
// if cannot create class of request by declared type - incoming data is bad
                $socket->dbg("BAD DATA cannot create class of request by declared type: '$messageType'");
//                $socket->dbg(static::$dbgLvl, 'RequestEnum list: ' . var_export(MessageClassEnum::getItemsList(), true));
                return $socket->badData();
            }
        }

        return $socket->getInMessage()->addPacket($packet);
    }

    /**
     * @return int
     */
    public function getBufferSize() : int
    {
        return $this->declaredLen - $this->len + 1;
    }

    /**
     * @param string $packet
     * @return bool
     */
    private function addPacket(string $packet) : bool
    {
        $socket = $this->getSocket();

// if server is busy - not check incoming fields, quick answer 'busy' and disconnect
        if ($socket->isServerBusy()) {
            $socket->sendMessage(BusyResMessage::create($socket,[]));
            $socket->setCloseAfterSend();

            return false;
        }

        $this->str .= $packet;
        $this->len = strlen($this->str);

// check message len for maximum len
        if ($this->maxLen && $this->len > $this->maxLen) {
            $this->dbg("BAD DATA length $this->len more than maximum $this->maxLen for $this->name");
            return $socket->badData();
        }

// check message len for declared len
        if ($this->declaredLen !== null && $this->len > $this->declaredLen) {
            $this->dbg("BAD DATA length $this->len more than declared length " . $this->declaredLen . "for $this->name (1)");
            return $socket->badData();
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

        if ($this->declaredLen === null || $this->len < $this->declaredLen) {
            return false;
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

        if ($this->len >= $this->fieldObject->getPoint()) {
            [$length, $this->$property] = $this->fieldObject->unpackField($this->str);

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
}