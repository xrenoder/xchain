<?php
/**
 * Script daemonization
 */
class Daemon
{
    /** @var string $pidFile */
    private $pidFile = null;

    /** @var Log $log */
    private $log = null;

    /**
     * Daemon constructor.
     * @param string $logPath
     * @param string $runPath
     */
    public function __construct(string $logPath, string $runPath)
    {
        $this->pidFile = $runPath . 'pid';
        $this->log = new Log($logPath . 'daemon.log');
    }

    /**
     * Daemon start
     * @param string $localIp
     * @param integer $localPort
     * @param string $command
     * @return bool
     */
    public function start(string $localIp, int $localPort, string $command = null): bool
    {
        set_time_limit(0);

        return true;
    }
}