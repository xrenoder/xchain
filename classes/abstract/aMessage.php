<?php
/**
 * Base class for classes of messages between nodes
 */
abstract class aMessage extends aBaseApp implements iMessage, icMessage
{
    protected static $dbgLvl = Logger::DBG_MESS;

    /** @var int  */ /* override me */
    protected static $enumId;

    public function getSocket() : Socket {return $this->getParent();}

    private $str;
//    public function setStr($val) {$this->str = $val; return $this;}
    public function getStr() {return $this->str;}

    private $len;
//    public function setLen($val) {$this->len = $val; return $this;}
    public function getLen() {return $this->len;}

    abstract protected function handler() : Bool;
    abstract public static function createMessage() : string;

    public static function create(Socket $socket): aMessage
    {
        return new static($socket);
    }

    public static function spawn(Socket $socket, int $enumId): ?aMessage
    {
        if ($className = MessageEnum::getClassName($enumId)) {
            return $className::create($socket);
        }

        return null;
    }

    public static function parser(Socket $socket, string $packet) : bool
    {
        if (!$socket->getMessage()) {
            $socket->addMessageStr($packet);

            $messageStr = $socket->getMessageStr();

            if (static::preHandler($socket, $messageStr) === false) {
                return false;
            }

            if (!($message = self::spawn($socket, static::getType($messageStr)))) {
// if cannot create class of request by declared type - incoming data is bad
                $messageType = static::getType($messageStr);
                $socket->dbg(static::$dbgLvl, "BAD DATA cannot create class of request by declared type: '$messageType'");
                $socket->dbg(static::$dbgLvl, 'RequestEnum list: ' . var_export(MessageEnum::getItemsList(), true));
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

    private static function preHandler(Socket $socket, string $str): bool
    {
        $len = strlen($str);

// if data length less than need for get declared length - return and wait more packets
        if ($len < self::getLengthLen()) {
            return false;
        }

        $declaredLen = aMessage::getLength($str);


// if real request length more than declared length - incoming data is bad
        if ($len > $declaredLen) {
            $socket->dbg(static::$dbgLvl,"BAD DATA real request length $len more than declared length $declaredLen: " . $str);
            return $socket->badData();
        }

        if ($len < static::getSpawnLen()) {
            return false;
        }

        return true;
    }

    private function addPacket(string $packet): bool
    {
        $this->str .= $packet;
        $this->len = strlen($this->str);
        return $this->handler();
    }
}