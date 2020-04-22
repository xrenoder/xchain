<?php
/**
 * Base class for all other, used App object
 */

class AppBase
{
    /** @var App */
    private $app;
    protected function setApp($val) {$this->app = $val; return $this;}
    protected function getApp() {return $this->app;}

    /**
     * AppBase constructor.
     * @param App $app
     */
    protected function __construct(App $app)
    {
        $this->setApp($app);
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