<?php
/**
 * Base class for all other, used App object
 */

abstract class AppBase
{
    /** @var App */
    private $app;
    protected function getApp() {return $this->app;}

    /** @var AppBase */
    private $parent;
    protected function getParent() {return $this->parent;}

    abstract public static function create(AppBase $parent) : AppBase;

    /**
     * AppBase constructor.
     * @param AppBase $parent
     */
    protected function __construct(AppBase $parent)
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
    protected function log(string $message): void
    {
        $this->getApp()->getLogger()->simpleLog($message);
    }

    /**
     * Short error logging
     * @param string $message
     */
    protected function err(string $message): void
    {
        $this->getApp()->getLogger()->errorLog($message);
    }

    /**
     * Short debug logging
     * @param int $lvl
     * @param string $message
     */
    protected function dbg(int $lvl, $message): void
    {
        $this->getApp()->getLogger()->debugLog($lvl, $message);
    }
}