<?php
/**
 * Base class for other application classes, uses App class (with Logger, Daemon, Server, Node etc)
 */
abstract class aBase implements iBase, icMessagesData
{
    /** @var App */
    private $app;
    protected function getApp() : App {return $this->app;}

    /** @var aBase */
    private $parent;
    protected function getParent() : aBase {return $this->parent;}

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
    public function dbg(int $lvl, string $message) : void
    {
        $this->getApp()->getLogger()->debugLog($lvl, $message);
    }
}