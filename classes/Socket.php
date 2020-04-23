<?php
/**
 * Socket work
 */
class Socket extends AppBase
{
    /** @var Server */
    private $server;
    public function setServer($val) {$this->server = $val; return $this;}
    public function getServer() {return $this->server;}

    private $fd;
    public function setFd($val) {$this->fd = $val; return $this;}
    public function getFd() {return $this->fd;}

    private $key;
    public function setKey($val) {$this->key = $val; return $this;}
    public function getKey() {return $this->key;}

    public static function create(Server $server, $fd, string $key = null): Socket
    {
        $app = $server->getApp();
        $me = new self($app);

        $me->setServer($server);
        $me->setFd($fd);
        $me->setKey($key);

        $me->getServer()->setSocket($me, $key);

        return $me;
    }
}