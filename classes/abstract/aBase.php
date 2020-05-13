<?php
/**
 * Base class for other application classes, uses App class (with Logger, Daemon, Server, Node etc)
 */
abstract class aBase
{
    protected static $dbgLvl = 0;   /* override me */

    /** @var App */
    private $app;
    protected function getApp() : App {return $this->app;}

    /** @var aBase */
    private $parent;
    protected function getParent() : aBase {return $this->parent;}

    public function getMyNodeId() : ?int {return $this->app->getMyNode()->getId();}

    /**
     * AppBase constructor.
     * @param aBase $parent
     */
    protected function __construct(aBase $parent)
    {
        $this->parent = $parent;
        $app = $parent;

        while (!is_a($app, 'App')) {
            $app = $app->getParent();
        }

        $this->app = $app;
    }

    /**
     * Short work logging
     * @param string $message
     */
    public function log(string $message) : void
    {
        $this->getApp()->getLogger()->simpleLog($message);
    }

    /**
     * Short error logging
     * @param string $message
     */
    public function err(string $message) : void
    {
        $this->getApp()->getLogger()->errorLog($message);
    }

    /**
     * Short debug logging
     * @param int $lvl
     * @param string $message
     */
    public function dbg(string $message) : void
    {
        $this->getApp()->getLogger()->debugLog(static::$dbgLvl, $message);
    }

    protected function dbTrans() : string
    {
        return $this->getApp()->getDba()->transactionBegin();
    }

    protected function dbCommit(string $transactionKey)
    {
        $this->getApp()->getDba()->transactionCommit($transactionKey);
    }
}