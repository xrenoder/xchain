<?php
/**
 * Script daemonization
 */
class Daemon extends AppBase
{
    /** @var string */
    private $pidFile;
    public function setPidFile($val) {$this->pidFile = $val; return $this;}
    public function getPidFile() {return $this->pidFile;}

    /** @var string */
    private $runPath;
    public function setRunPath($val) {$this->runPath = $val; return $this;}
    public function getRunPath() {return $this->runPath;}

    private const READ_BUFFER = 8192;
    private const CMD_RESTART = 'restart';				// command to force daemon restart
    private const CMD_STOP = 'stop';					// command to force daemon stop
    private const PS_COMMAND = "ps fuwww -p";
    private const KILL_TIMEOUT = 10;

    /** @var int */
    private $pid = null;
    public function getPid() {return $this->pid;}

    /** @var string[] */
    private static $signals = array (
        SIGHUP => 'signalSoftExit',
        SIGTERM => 'signalHardExit',
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
     * @return Daemon
     */
    public static function create(App $app, string $logPath, string $runPath): Daemon
    {
        $me = new self($app);

        $me->setRunPath($runPath);
        $me->setPidFile($runPath . 'pid');

        $me->getApp()->setDaemon($me);

        return $me;
    }

    /**
     * Daemon start or stop or restart
     * @param string $command
     * @return bool
     */
    public function run(string $command = null): bool
    {
        set_time_limit(0);

        $fd = fopen($this->pidFile, 'c+b');
        flock($fd, LOCK_EX);			// lock file to daemon will be started or exit
        $oldPid = fread($fd, static::READ_BUFFER);
        fseek($fd, 0);

        if ($oldPid) {
            if ($command !== static::CMD_RESTART && $command !== static::CMD_STOP) {
// exit if daemon is alive
                if ($this->getApp()->getServer()->isDaemonAlive()) {
                    flock($fd, LOCK_UN);
                    fclose($fd);
                    echo "Daemon alive ($oldPid) \n";
                    exit(0);
                }
            }

            $this->log("Daemon will be killed (pid $oldPid)");
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

            $this->log("Daemon will be started (pid $this->pid)");
            exit(0);
        }

// read pid of new daemon from file
        $fd = fopen($this->pidFile, 'rb');
        flock($fd, LOCK_SH);
        $this->pid = fread($fd, filesize($this->pidFile));
        flock($fd, LOCK_UN);
        fclose($fd);

        $this->log("Daemon started (pid $this->pid)");

// unmount console and standard IO channels, set new PHP error log
        posix_setsid();
        chdir('/');
        ini_set('error_log', $this->getApp()->getLogger()->getPhpErrFile());

        fclose(STDIN);
        $stdIn = fopen('/dev/null', 'rb');
        fclose(STDOUT);
        $stdOut = fopen($this->runPath . 'stdout', 'ab');
        fclose(STDERR);
        $stdErr = fopen($this->runPath . 'stderr', 'ab');

// set signal handlers
        foreach(static::$signals as $signal => $handler) {
            pcntl_signal($signal, array($this, $handler));
//            $this->log("Sig $signal : "  . var_export(pcntl_signal_get_handler($signal), true));
        }

        pcntl_async_signals(true);

        return true;
    }

// прибиваем демона сигналами: мягкий, жесткий, контрольный в голову
    private function kill($pid) {
        foreach(static::$kills as $sig) {
            $nokill = 1;

            $checkCmd = self::PS_COMMAND . " " . $pid;
            $check = shell_exec($checkCmd);

            if (strpos($check, $this->getApp()->getName()) !== false) {
                $killCmd = "kill -$sig " . $pid;
                $this->log("Old daemon process $pid will be killed by SIG$sig  : " . $killCmd);
                $nokill = $this->commandExec($killCmd, 0);
            } else {
                $this->log("Old daemon process $pid is dead");
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
     * Signal SIGTERM handler
     * @param $signo
     * @param null $pid
     * @param null $status
     */
    public function signalHardExit($signo) {
        pcntl_signal($signo, SIG_IGN);
        $this->log("Hard finish by signal $signo");
        $this->getApp()->getServer()->hardFinish();
    }

    /**
     * Signal SIGHUP handler
     * @param $signo
     * @param null $pid
     * @param null $status
     */
    public function signalSoftExit($signo) {
        pcntl_signal($signo, SIG_IGN);
        $this->log("Soft finish by signal");
        $this->getApp()->getServer()->softFinish();
    }
}