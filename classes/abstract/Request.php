<?php
/**
 * Incoming Request base class
 */
abstract class Request extends AppBase
{
    protected static $dbgLvl = Logger::DBG_REQ;

    /** @var int  */ /* override me */
    protected static $enumId;

    protected const FLD_LENGTH_LEN = 4;
    protected const FLD_LENGTH_FMT = 'N';   //unsigned long big-endian

    protected const FLD_TYPE_LEN = 4;
    protected const FLD_TYPE_FMT = 'N';     //unsigned long big-endian

//    protected $socket;
//    public function setSocket($val) {$this->socket = $val; return $this;}
    public function getSocket() {return $this->getParent();}

    private $str;
//    public function setStr($val) {$this->str = $val; return $this;}
    public function getStr() {return $this->str;}

    private $len;
//    public function setLen($val) {$this->len = $val; return $this;}
    public function getLen() {return $this->len;}

    abstract protected function handler() : Bool;
    abstract public static function createMessage() : string;

    public static function create(Socket $socket): Request
    {
        $me = new static($socket);

        return $me;
    }

    public static function spawn(Socket $socket, int $enumId): ?Request
    {
        if (!RequestEnum::isSet($enumId)) {
            return null;
        }

        $className = RequestEnum::getItem($enumId);

        if (!is_a($className, __CLASS__, true)) {
            throw new Exception( "$className is not instance of Request class");
        }

        return $className::create($socket);
    }

    public function addPacket(string $packet): bool
    {
        $this->str .= $packet;
        $this->len = strlen($this->str);
        return $this->handler();
    }

    public static function getLengthLen()
    {
        return static::FLD_LENGTH_LEN;
    }

    public static function getSpawnLen()
    {
        return static::FLD_LENGTH_LEN + static::FLD_TYPE_LEN;
    }

    public static function getLength(string $data) : int
    {
        $offset = 0;
        $tmp = unpack(static::FLD_LENGTH_FMT, substr($data, $offset, static::FLD_LENGTH_LEN));
        return $tmp[1];
    }

    public static function getType(string $data) : int
    {
        $offset = static::FLD_LENGTH_LEN;
        $tmp = unpack(static::FLD_TYPE_FMT, substr($data, $offset, static::FLD_TYPE_LEN));
        return $tmp[1];
    }
}