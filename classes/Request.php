<?php
/**
 * Incoming Request
 */
class Request extends AppBase
{
    public const FLD_LENGTH_LEN = 4;
    public const FLD_TYPE_LEN = 4;

    private $socket;
    public function setSocket($val) {$this->socket = $val; return $this;}
    public function getSocket() {return $this->socket;}

    private $str;
    public function setStr($val) {$this->str = $val; return $this;}
    public function addStr($val) {$this->str .= $val; return $this;}
    public function getStr() {return $this->str;}

    private $len;
    public function setLen($val) {$this->len = $val; return $this;}
    public function getLen() {return $this->len;}

    public static function create(Socket $socket, string $packet): Request
    {
        $me = new self($socket->getServer()->getApp());

        $me
            ->setSocket($socket)
            ->setstr($packet)
            ->setLen(strlen($packet));

        return $me;
    }
}