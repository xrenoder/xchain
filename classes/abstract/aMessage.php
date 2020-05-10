<?php
/**
 * Base class for classes of messages between nodes
 */
abstract class aMessage extends aBase
{
    protected static $dbgLvl = Logger::DBG_MESS;

    public const MY_NODE_ID = 'myNodeId';

    /** @var int  */
    protected static $id;  /* override me */

    /**
     * If false - this message can be first sended or received after socket creation
     * @var bool
     */
    protected static $needAliveCheck = true; /** can be overrided or not */

    public function getSocket() : Socket {return $this->getParent();}

    /** @var string */
    protected $name;
    public function setName() : self {$this->name = MessageClassEnum::getItem(static::$id); return $this;}
    public function getName() : string {return $this->name;}

    /** @var int  */
    protected $maxLen = null;
    public function setMaxLen() : self {$this->maxLen = MessageClassEnum::getMaxLen(static::$id); return $this;}
    public function getMaxLen() : int {return $this->maxLen;}

    /** @var string */
    private $str;

    /** @var int  */
    protected $len = null;
    public function getLen() : int {return $this->len;}

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fields = array(
        MessageFieldClassEnum::MESS_FLD_TYPE =>      '',
        MessageFieldClassEnum::MESS_FLD_LENGTH =>    'declaredLen',
        MessageFieldClassEnum::MESS_FLD_TIME =>      'sendingTime',
        MessageFieldClassEnum::MESS_FLD_NODE =>      'remoteNodeId',
    );

    /** @var int  */
    private $fieldCounter = 1;      // not 0 - zero is id of field 'message type'

    /** @var aMessageField  */
    private $fieldObject = null;

    /** @var int  */
    protected $declaredLen = null;
    public function getDeclaredLen() : int {return $this->declaredLen;}

    /** @var int  */
    private $sendingTime = null;
    public function getSendingTime() : int {return $this->sendingTime;}

    /** @var int  */
    private $remoteNodeId = null;
    public function getRemoteNodeId() : int {return $this->remoteNodeId;}

    abstract public static function createMessage(array $data) : string;
    abstract protected function incomingMessageHandler() : bool;

    /**
     * @param Socket $socket
     * @return aMessage|null
     */
    protected static function create(Socket $socket) : ?self
    {
        if (static::$needAliveCheck && !$socket->isAliveChecked()) {
            $socket->dbg(MessageClassEnum::getItem(static::$id) . ' cannot explored before Alive checking');
            return null;
        }

        $socket->dbg(MessageClassEnum::getItem(static::$id) .  ' detected');

        $me = new static($socket);

        $me
            ->setMaxLen()
            ->setName();

        $socket->setMessage($me);

        return $me;
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
            return $className::create($socket);
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
        if (!$socket->getMessage()) {
            $messageType = MessageFieldClassEnum::prepareField(0, $packet);

            if (!($message = self::spawn($socket, $messageType))) {
// if cannot create class of request by declared type - incoming data is bad
                $socket->dbg("BAD DATA cannot create class of request by declared type: '$messageType'");
//                $socket->dbg(static::$dbgLvl, 'RequestEnum list: ' . var_export(MessageClassEnum::getItemsList(), true));
                return $socket->badData();
            }
        }

        return $socket->getMessage()->addPacket($packet);
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
            $socket->addOutData(BusyResMessage::createMessage(array(static::MY_NODE_ID => $this->getMyNodeId())));
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
        foreach (static::$fields as $fieldId => $property) {
            if ($this->fieldCounter > $fieldId) {
                continue;
            }

            if (!$this->prepareField($fieldId, $property)) {
// if field cannot be prepared - break  (not 'return false'), may be all fields was prepared
                break;
            }
        }

        if ($this->len < $this->declaredLen) {
            return false;
        }

        return $this->incomingMessageHandler();
    }

    protected function prepareField(int $fieldId, string $property) : bool
    {
        if ($this->$property !== null) return true;

        if ($this->fieldObject === null) {
            $this->fieldObject = aMessageField::spawn($this, $fieldId);
        }

        if ($this->len >= $this->fieldObject->getPoint()) {
            $this->$property = $this->fieldObject->unpackField($this->str);
            $this->dbg("Prepare field $fieldId : $property = " . $this->$property);
            $result = $this->fieldObject->check();
            $this->fieldObject = null;

            $this->fieldCounter++;

            return $result;
        }

        return false;
    }
}