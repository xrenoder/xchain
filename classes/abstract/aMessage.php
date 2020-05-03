<?php
/**
 * Base class for classes of messages between nodes
 */
abstract class aMessage extends aBase implements icMessageFields
{
    protected static $dbgLvl = Logger::DBG_MESS;

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

    /** @var int  */
    protected $maxLen = null;
    public function setMaxLen() : self {$this->maxLen = MessageClassEnum::getMaxLen(static::$id); return $this;}

    /** @var string */
    private $str;

    /** @var int  */
    protected $len = null;
    public function getLen() : int {return $this->len;}

    /** @var int  */
    private $fieldCounter = 1;

    protected static $fields = array(
        self::MESS_TYPE =>      array('',               'checkEmpty'),
        self::MESS_LENGTH =>    array('declaredLen',    'checkLength'),
        self::MESS_NODE =>      array('remoteNodeId',   'checkNode'),
    );

    /** @var int  */
    protected $declaredLen = null;
    public function getDeclaredLen() : int {return $this->declaredLen;}

    /** @var int  */
    private $remoteNodeId = null;

    abstract public static function createMessage(array $data) : string;
    abstract protected function incomingMessageHandler() : bool;

    /**
     * @param Socket $socket
     * @return aMessage|null
     */
    protected static function create(Socket $socket) : ?aMessage
    {
        if (static::$needAliveCheck && !$socket->isAliveChecked()) {
            $socket->dbg(static::$dbgLvl,MessageClassEnum::getItem(static::$id) . ' cannot explored before Alive checking');
            return null;
        }

        $socket->dbg(static::$dbgLvl,MessageClassEnum::getItem(static::$id) .  ' detected');

        $me = new static($socket);

        $me->setMaxLen();
        $me->setName();

        return $me;
    }

    /**
     * @param Socket $socket
     * @param int $id
     * @return iMessage|null
     * @throws Exception
     */
    public static function spawn(Socket $socket, int $id) : ?aMessage
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
            $messageType = MessFldEnum::prepareField(0, $packet);

            if (!($message = self::spawn($socket, $messageType))) {
// if cannot create class of request by declared type - incoming data is bad
                $socket->dbg(static::$dbgLvl, "BAD DATA cannot create class of request by declared type: '$messageType'");
//                $socket->dbg(static::$dbgLvl, 'RequestEnum list: ' . var_export(MessageClassEnum::getItemsList(), true));
                return $socket->badData();
            }

            $socket->setMessage($message);
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
        $this->str .= $packet;
        $this->len = strlen($this->str);

// check message len for maximum len
        if ($this->maxLen && $this->len > $this->maxLen) {
            $this->dbg(static::$dbgLvl,"BAD DATA length $this->len more than maximum $this->maxLen for $this->name");
            return $this->getSocket()->badData();
        }

// check message len for declared len
        if ($this->declaredLen && $this->len > $this->declaredLen) {
            $this->dbg(static::$dbgLvl,"BAD DATA length $this->len more than declared length $this->declaredLen for $this->name (1)");
            return $this->getSocket()->badData();
        }

// prepare fields
        foreach (static::$fields as $fieldId => $props) {
            if ($this->fieldCounter > $fieldId) {
                continue;
            }

            [$property, $checker] = $props;

            if (!$this->prepareField($property, $checker)) {
// if field cannot be prepared - break  (not 'return false'), may be all fields was readed
                break;
            }
        }

//
        if ($this->len < $this->declaredLen) {
            return false;
        }

        return $this->incomingMessageHandler();
    }

    protected function prepareField(string $property, string $checker) : bool
    {
        if ($this->$property !== null) return true;

        if ($this->len >= MessFldEnum::getPoint($this->fieldCounter)) {
            $this->$property = MessFldEnum::prepareField($this->fieldCounter, $this->str);
            $this->dbg(static::$dbgLvl,"Prepare field $this->fieldCounter : $property = " . $this->$property);
            $this->fieldCounter++;
            return $this->$checker();
        }

        return false;
    }

    // check declared len for maximum len
    private function checkLength() : bool
    {
        if ($this->maxLen && $this->declaredLen > $this->maxLen) {
            $this->dbg(static::$dbgLvl,"BAD DATA declared length $this->declaredLen more than maximum $this->maxLen for $this->name");
            return $this->getSocket()->badData();
        }

        if ($this->len > $this->declaredLen) {
            $this->dbg(static::$dbgLvl,"BAD DATA length $this->len more than declared length $this->declaredLen for $this->name (2)");
            return $this->getSocket()->badData();
        }

        return true;
    }

    private function checkNode() : bool
    {
        $this->getSocket()->setRemoteNode(aNode::spawn($this->getApp(), $this->remoteNodeId));
        $this->getSocket()->checkNodesCompatiblity();
// TODO добавить проверку в блокчейне, может ли отправитель сообщения исполнять роль той ноды, которой представляется
        return true;
    }

    private function checkEmpty() : bool
    {
        return true;
    }
}