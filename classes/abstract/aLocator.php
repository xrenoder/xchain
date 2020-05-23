<?php


class aLocator extends aBase
{
    /** @var string */
    private $name;
    public function setName(string $val) : self {$this->name = $val; return $this;}
    public function getName() : string {return $this->name;}

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