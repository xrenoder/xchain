<?php
/**
 * Base class for classes of messages between nodes
 */
abstract class aMessage extends aBaseApp implements iMessage, icMessage
{
    protected static $dbgLvl = Logger::DBG_MESS;

    /** @var int  */
    protected static $enumId;   /* override me */

    /** @var string */
    protected static $name = 'NotDeclaredMessageName'; /* override me */

    public function getSocket() : Socket {return $this->getParent();}

    private $str;
//    public function setStr($val) {$this->str = $val; return $this;}
//    public function getStr() {return $this->str;}

    private $len;
//    public function setLen($val) {$this->len = $val; return $this;}
//    public function getLen() {return $this->len;}

    protected static $needAliveCheck = true;

    abstract protected function incomingMessageHandler() : Bool;
    abstract public static function createMessage() : string;

    public static function create(Socket $socket): ?iMessage
    {
        if (static::$needAliveCheck && !$socket->isAliveChecked()) {
            $socket->dbg(static::$dbgLvl,static::$name .  ' cannot explored before Alive checking');
            return null;
        }

        return new static($socket);
    }

    public static function spawn(Socket $socket, int $enumId): ?iMessage
    {
        if ($className = MessageClassEnum::getClassName($enumId)) {
            return $className::create($socket);
        }

        return null;
    }

    public static function parser(Socket $socket, string $packet) : bool
    {
        if (!$socket->getMessage()) {
            $socket->addMessageStr($packet);

            $messageStr = $socket->getMessageStr();

            if (($messageType = static::preHandler($socket, $messageStr)) === 0) {
                return false;
            }

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

    protected static function getLengthLen() : int
    {
        return static::FLD_LENGTH_LEN;
    }

    protected static function getSpawnLen() : int
    {
        return static::FLD_LENGTH_LEN + static::FLD_TYPE_LEN;
    }

    protected static function getLength(string $data) : int
    {
        $offset = 0;
        $tmp = unpack(static::FLD_LENGTH_FMT, substr($data, $offset, static::FLD_LENGTH_LEN));
        return $tmp[1];
    }

    protected static function getType(string $data) : int
    {
        $offset = static::FLD_LENGTH_LEN;
        $tmp = unpack(static::FLD_TYPE_FMT, substr($data, $offset, static::FLD_TYPE_LEN));
        return $tmp[1];
    }

    private static function preHandler(Socket $socket, string $str): int
    {
        $len = strlen($str);

// if data length less than need for get declared length - return and wait more packets
        if ($len < self::getLengthLen()) {
            return 0;
        }

        $declaredLen = aMessage::getLength($str);

// if real request length more than declared length - incoming data is bad
        if ($len > $declaredLen) {
            $socket->dbg(static::$dbgLvl,"BAD DATA real request length $len more than declared length $declaredLen: " . $str);
            $socket->badData();
            return 0;
        }

        if ($len < static::getSpawnLen()) {
            return 0;
        }

        $messageType = static::getType($str);
        $messageMaxLen = MessageClassEnum::getMaxMessageLen($messageType);

        if ($messageMaxLen && $declaredLen > $messageMaxLen) {
            $socket->dbg(static::$dbgLvl,"BAD DATA declared length $declaredLen more than maximum $messageMaxLen for declared type: $messageType");
            $socket->badData();
            return 0;
        }

        return $messageType;
    }

    private function addPacket(string $packet): bool
    {
        $this->str .= $packet;
        $this->len = strlen($this->str);
        return $this->incomingMessageHandler();
    }
}