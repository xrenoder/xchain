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
     * @param $message
     */
    protected function log($message) {
        $this->getApp()->getLogger()->simpleLog($message);
    }

    /**
     * Short error logging
     * @param $message
     */
    protected function err($message) {
        $this->getApp()->getLogger()->errorLog($message);
    }

    public function getAppName() {
        return $this->getApp()->getName();
    }
}