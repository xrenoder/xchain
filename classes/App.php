<?php
/**
 * Application class
 */
class App extends aBase
{
    /** @var string */
    private $name;
    public function setName(string $val) : self {$this->name = $val; return $this;}
    public function getName() : string {return $this->name;}

    /** @var Logger */
    private $logger;
    public function setLogger(Logger $val) : self {$this->logger = $val; return $this;}
    public function getLogger() : Logger {return $this->logger;}

    /** @var Daemon */
    private $daemon;
    public function setDaemon(Daemon $val) : self {$this->daemon = $val; return $this;}
    public function getDaemon() : Daemon {return $this->daemon;}

    /** @var Server */
    private $server;
    public function setServer(Server $val): self {$this->server = $val; return $this;}
    public function getServer() : Server {return $this->server;}

    /** @var aNode */
    private $node;
    public function setNode(aNode $val) : self {$this->node = $val; return $this;}
    public function getNode() : aNode {return $this->node;}

    /**
     * App constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($this);

        $this
            ->setName($name)
            ->setNode(aNode::spawn($this, NodeClassEnum::CLIENT_ID));
    }
}
