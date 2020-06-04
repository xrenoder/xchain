<?php
/**
 * Base classenum for other application classes, uses App classenum (with Logger, Daemon, Server, Node etc)
 */
abstract class aBase
{
    protected static $dbgLvl = Logger::DBG_LOCATOR; /* override me */

    /** @var aLocator */
    private $locator = null;
    public function setLocator(?aLocator $val) : self {$this->locator = $val; return $this;}
    protected function getLocator() : aLocator {return $this->locator;}

    /** @var aBase */
    private $parent = null;
    public function setParent(?aBase $val) : self {$this->parent = $val; return $this;}
    protected function getParent() : aBase {return $this->parent;}

    public function getMyNodeId() : ?int {return $this->locator->getMyNode()->getId();}

    /**
     * AppBase constructor.
     * @param aBase $parent
     */
    protected function __construct(aBase $parent)
    {
        $this->parent = $parent;
        $locator = $parent;

        while (!$locator instanceof aLocator) {
            $locator = $locator->getParent();
        }

        $this->locator = $locator;
    }

    /**
     * Short work logging
     * @param string $message
     */
    public function log(string $message) : void
    {
        $this->getLocator()->getLogger()->simpleLog(static::$dbgLvl, $message);
    }

    /**
     * Short error logging
     * @param string $message
     */
    public function err(string $message) : void
    {
        $this->getLocator()->getLogger()->errorLog(static::$dbgLvl, $message);
    }

    /**
     * Short debug logging
     * @param int $lvl
     * @param string $message
     */
    public function dbg(string $message) : void
    {
        $this->getLocator()->getLogger()->debugLog(static::$dbgLvl, $message);
    }

    protected function dbTrans() : string
    {
        return $this->getLocator()->getDba()->transactionBegin();
    }

    protected function dbCommit(string $transactionKey)
    {
        $this->getLocator()->getDba()->transactionCommit($transactionKey);
    }
}