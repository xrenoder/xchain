<?php
/**
 * Application class
 */
class App extends aBase
{
    /** @var string */
    private $name;
    public function getName() : string {return $this->name;}

    /** @var int */
    private $pid = null;
    public function setPid($val) : self {$this->pid = $val; return $this;}
    public function getPid() : int {return $this->pid;}

    /** @var Logger */
    private $logger;
    public function setLogger(Logger $val) : self {$this->logger = $val; return $this;}
    public function getLogger() : Logger {return $this->logger;}

    /** @var DBA */
    private $dba;
    public function setDba(DBA $val) : self {$this->dba = $val; return $this;}
    public function getDba() : DBA {return $this->dba;}

    /** @var Daemon */
    private $daemon;
    public function setDaemon(Daemon $val) : self {$this->daemon = $val; return $this;}
    public function getDaemon() : Daemon {return $this->daemon;}

    /** @var Server */
    private $server;
    public function setServer(Server $val): self {$this->server = $val; return $this;}
    public function getServer() : Server {return $this->server;}

    /** @var aNode */
    private $myNode;
    public function setMyNode(aNode $val) : self {$this->myNode = $val; return $this;}
    public function getMyNode() : aNode {return $this->myNode;}

    /** @var Address */
    private $myAddr;
    public function setMyAddr(Address $val) : self {$this->myAddr = $val; return $this;}
    public function getMyAddr() : Address {return $this->myAddr;}

    /** @var ChainSummaryData */
    private $chainSummaryData;
    public function setChainSummaryData(ChainSummaryData $val) : self {$this->chainSummaryData = $val; return $this;}
    public function getChainSummaryData() : ChainSummaryData {return $this->chainSummaryData;}

    /**
     * App constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($this);

        $this->name = $name;
        $this->pid = posix_getpid();
    }
}
