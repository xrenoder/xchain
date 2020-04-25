<?php
/**
 * Incoming Request base class
 */
abstract class Request extends AppBase
{
    protected const FLD_LENGTH_LEN = 4;
    protected const FLD_LENGTH_FMT = 'L';

    protected const FLD_TYPE_LEN = 4;
    protected const FLD_TYPE_FMT = 'L';

    protected $enumId;
    public function setEnumId($val) {$this->enumId = $val; return $this;}
    public function getEnumId() {return $this->enumId;}

    protected $socket;
    public function setSocket($val) {$this->socket = $val; return $this;}
    public function getSocket() {return $this->socket;}

    private $str;
//    public function setStr($val) {$this->str = $val; return $this;}
    public function getStr() {return $this->str;}

    private $len;
//    public function setLen($val) {$this->len = $val; return $this;}
    public function getLen() {return $this->len;}

    abstract protected function handler() : Bool;
    abstract public static function createMessage() : string;

    public static function create(Socket $socket, int $enumId): Request
    {
        $me = new static($socket->getServer()->getApp());

        $me
            ->setSocket($socket)
            ->setEnumId($enumId);

        return $me;
    }

    public static function spawn(Socket $socket, int $enumId): ?Request
    {
        if (!RequestEnum::isSet($enumId)) {
            return null;
        }

        $className = RequestEnum::getItem($enumId);

        if (!is_a($className, __CLASS__)) {
            throw new Exception( "$className is not instance of Request class");
        }

        return new $className($socket, $enumId);
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

    public static function getLength(string $data)
    {
        $offset = 0;
        [$len] = unpack(static::FLD_LENGTH_FMT, substr($data, $offset, static::FLD_LENGTH_LEN));
        return $len;
    }

    public static function getType(string $data) : int
    {
        $offset = static::FLD_LENGTH_LEN;
        [$type] = unpack(static::FLD_TYPE_FMT, substr($data, $offset, static::FLD_TYPE_LEN));
        return $type;
    }
}