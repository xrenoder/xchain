<?php
/**
 * Base class for other application classes, uses App class
 */

abstract class aBaseApp
{
    /** @var App */
    private $app;
    protected function getApp() {return $this->app;}

    /** @var aBaseApp */
    private $parent;
    protected function getParent() {return $this->parent;}

    /**
     * AppBase constructor.
     * @param aBaseApp $parent
     */
    protected function __construct(aBaseApp $parent)
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
    public function log(string $message): void
    {
        $this->getApp()->getLogger()->simpleLog($message);
    }

    /**
     * Short error logging
     * @param string $message
     */
    public function err(string $message): void
    {
        $this->getApp()->getLogger()->errorLog($message);
    }

    /**
     * Short debug logging
     * @param int $lvl
     * @param string $message
     */
    public function dbg(int $lvl, $message): void
    {
        $this->getApp()->getLogger()->debugLog($lvl, $message);
    }
}