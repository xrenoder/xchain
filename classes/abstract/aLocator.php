<?php


abstract class aLocator extends aBase
{
    abstract public function isWorker() : bool;

    /** @var string */
    private $name;
    public function setName(string $val) : self {$this->name = $val; return $this;}
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

    /** @var aNode */
    private $myNode;
    public function setMyNode(aNode $val) : self {$this->myNode = $val; return $this;}
    public function getMyNode() : aNode {return $this->myNode;}

    /** @var Address */
    private $myAddress;
    public function setMyAddress(Address $val) : self {$this->myAddress = $val; return $this;}
    public function getMyAddress() : Address {return $this->myAddress;}

    /** @var SummaryDataSet */
    private $summaryDataSet;
    public function setSummaryDataSet(SummaryDataSet $val) : self {$this->summaryDataSet = $val; return $this;}
    public function getSummaryDataSet() : SummaryDataSet {return $this->summaryDataSet;}

    /**
     * App constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($this);

        $this->setName($name);
        $this->pid = posix_getpid();
    }

    /**
     * Collect garbage for optimal memory usage
     */
    public function garbageCollect() : void
    {
//        gc_enable();
        $gcCycles = gc_collect_cycles();
        $gcMemCaches = gc_mem_caches();
//        gc_disable();

        if ($gcCycles || $gcMemCaches) {
            $this->dbg("Garbage collect: $gcCycles cycles & $gcMemCaches bytes of memory was cleaned");
        }
    }
}