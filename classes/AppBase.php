<?php
/**
 * Base class for all other, used App object
 */

class AppBase
{
    /** @var App */
    protected $app = null;

    /**
     * AppBase constructor.
     * @param App $app
     */
    protected function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Short work logging
     * @param $message
     */
    protected function log($message) {
        $this->app->logger->simpleLog($message);
    }

    /**
     * Short error logging
     * @param $message
     */
    protected function err($message) {
        $this->app->logger->errorLog($message);
    }

    public function getAppName() {
        return $this->app->getName();
    }
}