<?php
/**
 * Script daemonization
 */
class Daemon extends AppBase
{
    private const READ_BUFFER = 8192;
    private const CMD_RESTART = 'restart';				// command to force daemon restart
    private const CMD_STOP = 'stop';					// command to force daemon stop
    private const PS_COMMAND = "ps fuwww -p";
    private const KILL_TIMEOUT = 10;

    private const SIG_HUP = 1;
    private const SIG_TERM = 15;

    /** @var string */
    private $pidFile = null;

    /** @var string */
    private $phpErrLogFile = null;

    /** @var int */
    private $pid = null;

    /** @var string[] */
    private static $signals = array (
        self::SIG_HUP => 'signalSoftExit',
        self::SIG_TERM => 'signalHardExit',
    );

    private static $kills = array (		// последовательность сигналов при убивании зависшего демона
        "HUP",
        "TERM",
        "KILL"
    );

    /**
     * Daemon constructor.
     * @param App $app
     * @param string $logPath
     * @param string $runPath
     */
    public function __construct(App $app, string $logPath, string $runPath)
    {
        parent::__construct($app);
        $this->pidFile = $runPath . 'pid';
        $this->phpErrLogFile = $logPath . 'php.err';
    }

    /**
     * Daemon start or stop or restart
     * @param string $localIp
     * @param integer $localPort
     * @param string $command
     * @return bool
     */
    public function start(string $command = null): bool
    {
        set_time_limit(0);

        $fd = fopen($this->pidFile, 'c+b');
        flock($fd, LOCK_EX);			// lock file to daemon will be started or exit
        $oldPid = fread($fd, static::READ_BUFFER);
        fseek($fd, 0);

        if ($oldPid) {
            if ($command !== static::CMD_RESTART && $command !== static::CMD_STOP) {
// exit if daemon is alive
                if ($this->app->server->isDaemonAlive()) {
                    flock($fd, LOCK_UN);
                    fclose($fd);
                    echo "Daemon alive ($oldPid) \n";
                    exit(0);
                }
            }

            $this->simpleLog("Daemon will be killed (pid $oldPid)");
            $this->kill($oldPid);
        }

        if ($command === static::CMD_STOP) {
            ftruncate($fd, 0);
            flock($fd, LOCK_UN);
            fclose($fd);
            exit(0);
        }

// daemonization
        $this->pid = pcntl_fork();

        if ($this->pid) {
// write new daemon pid to file and exit
            ftruncate($fd, 0);
            fwrite($fd, $this->pid);
            flock($fd, LOCK_UN);
            fclose($fd);

            $this->simpleLog("Daemon will be started (pid $this->pid)");
            exit(0);
        }

// read pid of new daemon from file
        $fd = fopen($this->pidFile, 'r');
        flock($fd, LOCK_SH);
        $this->pid = fread($fd, filesize($this->pidFile));
        flock($fd, LOCK_UN);
        fclose($fd);

        $this->simpleLog("Daemon started (pid $this->pid)");

// unmount console and standard IO channels, set new PHP error log
        posix_setsid();
        chdir('/');
        ini_set('error_log', $this->phpErrLogFile);

        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);

// назначаем обработку сигналов
        declare(ticks = 1);

        foreach(static::$signals as $signal => $handler) {
            $sigHandler = array(&$this, $handler);
            pcntl_signal($signal, $sigHandler);
        }

        return true;
    }

// прибиваем зависшего демона сигналами: мягкий, жесткий, контрольный в голову
    private function kill($pid) {
        foreach(static::$kills as $sig) {
            $nokill = 1;

            $checkCmd = self::PS_COMMAND . " " . $pid;
            $check = shell_exec($checkCmd);

            if (strpos($check, $this->app->getName()) !== false) {
                $this->simpleLog("Old daemon process $pid will be killed by SIG$sig");
                $killCmd = "kill -$sig " . $pid;
                $nokill = $this->commandExec($killCmd, 0);
            } else {
                $this->simpleLog("Old daemon process $pid is dead");
                break;
            }

            if (!$nokill) {
                if ($sig !== "KILL") sleep(self::KILL_TIMEOUT);
            }
        }
    }

    private function commandExec($command, $normalExitStatus)
    {
        $output = array();
        $answer = "";

        exec($command, $output, $status);

        if ($normalExitStatus >=0 && $status != $normalExitStatus) {    //ошибка
            $answer = implode("\n", $output);
        } else if ($normalExitStatus < 0) {                             //ошибка если есть ответ
            $answer = @implode("\n", $output);
        }

        return $answer;
    }

    /**
     * Signal SIG_TERM handler
     * @param $signo
     * @param null $pid
     * @param null $status
     */
    public function signalHardExit($signo, $pid = null, $status = null) {
        pcntl_signal($signo, SIG_IGN);
        $this->simpleLog("Hard finish by signal $signo");
        $this->app->server->hardFinish();
    }

    /**
     * Signal SIG_HUP handler
     * @param $signo
     * @param null $pid
     * @param null $status
     */
    public function signalSoftExit($signo, $pid = null, $status = null) {
        pcntl_signal($signo, SIG_IGN);
        $this->simpleLog("Soft finish by signal $signo");
        $this->app->server->softFinish();
    }

    /**
     * @return int|null
     */
    public function getPid() {
        return $this->pid;
    }
}