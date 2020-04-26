<?php
/**
 * Daemonization
 */
class Daemon extends aBaseApp
{
    /** @var string */
    private $pidFile;
    public function setPidFile($val) {$this->pidFile = $val; return $this;}

    /** @var string */
    private $runPath;
    public function setRunPath($val) {$this->runPath = $val; return $this;}

    /** @var int */
    private $pid;
    public function setPid($val) {$this->pid = $val; return $this;}
    public function getPid() {return $this->pid;}

    private const READ_BUFFER = 8192;
    private const CMD_RESTART = 'restart';				// command to force daemon restart
    private const CMD_STOP = 'stop';					// command to force daemon stop
    private const PS_COMMAND = 'ps fuwww -p';
    private const KILL_TIMEOUT = 10;

    /** @var string[] */
    private static $signals = array (
        SIGHUP => 'signalSoftExit',
        SIGTERM => 'signalHardExit',
    );

    /** @var string[] */
    private static $kills = array (		// последовательность сигналов при убивании зависшего демона
        'HUP',
        'TERM',
        'KILL'
    );

    /**
     * Daemon constructor.
     * @param App $app
     * @param string $runPath
     * @param string $pidName
     * @return Daemon
     */
    public static function create(App $app, string $runPath, string $pidName): Daemon
    {
        $me = new self($app);

        $me->setRunPath($runPath);
        $me->setPidFile($runPath . $pidName);
        $me->setPid(posix_getpid());

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
            // exit if daemon is alive
            if ($command !== static::CMD_RESTART && $command !== static::CMD_STOP) {
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
        $pid = pcntl_fork();

        if ($pid) {
            $this->log("Daemon will be started (pid $pid)");

// write new daemon pid to file and exit
            ftruncate($fd, 0);
            fwrite($fd, $pid);
            flock($fd, LOCK_UN);
            fclose($fd);

            exit(0);
        }

// read pid of new daemon from file
        $fd = fopen($this->pidFile, "rb");
        flock($fd, LOCK_SH);
        $pid = fread($fd, filesize($this->pidFile));
        flock($fd, LOCK_UN);
        fclose($fd);

        $this->setPid(posix_getpid());

        if ($this->getPid() == $pid) {
            $this->log('Daemon started (pid ' . $this->getPid() . ')');
        } else {
            $this->err('Daemon cannot started: (pid ' . $this->getPid() . " is not equal pid $pid from pid-file)");
            exit(0);
        }


// unmount console and standard IO channels, set new PHP error log
        posix_setsid();
        chdir('/');
        ini_set('error_log', $this->getApp()->getLogger()->getPhpErrFile());

        fclose(STDIN);
        fopen('/dev/null', 'rb');
        fclose(STDOUT);
        fopen('/dev/null', 'ab');
        fclose(STDERR);
        fopen('/dev/null', 'ab');

// set signal handlers
        foreach(static::$signals as $signal => $handler) {
            pcntl_signal($signal, array($this, $handler));
        }

        pcntl_async_signals(true);

        return true;
    }

    /**
     * Kill daemon with signals: SIGHUP, SIGTERM, SIGKILL
     * @param $pid
     */
    private function kill($pid): void
    {
        foreach(static::$kills as $sig) {
            $nokill = 1;

            $checkCmd = self::PS_COMMAND . ' ' . $pid;
            $check = shell_exec($checkCmd);

            if (strpos($check, $this->getApp()->getName()) !== false) {
                $killCmd = "kill -$sig " . $pid;
                $this->log("Old daemon process $pid will be killed by SIG$sig  : " . $killCmd);
                $nokill = $this->commandExec($killCmd, 0);
            } else {
                $this->log("Old daemon process $pid is dead");
                break;
            }

            if (!$nokill && $sig !== 'KILL') {
                sleep(self::KILL_TIMEOUT);
            }
        }
    }

    /**
     * Execute command in *nix command line
     * @param $command
     * @param $normalExitStatus
     * @return string
     */
    private function commandExec($command, $normalExitStatus): string
    {
        $output = array();
        $answer = '';

        exec($command, $output, $status);

        if ($normalExitStatus >= 0 && $status !== $normalExitStatus) {    //ошибка
            $answer = implode("\n", $output);
        } else if ($normalExitStatus < 0) {                             //ошибка если есть ответ
            $answer = @implode("\n", $output);
        }

        return $answer;
    }

    /**
     * Signal SIGTERM handler
     * @param $signo
     */
    public function signalHardExit($signo): void
    {
        pcntl_signal($signo, SIG_IGN);
        $this->log("Hard finish by signal $signo");
        $this->getApp()->getServer()->hardFinish();
    }

    /**
     * Signal SIGHUP handler
     * @param $signo
     */
    public function signalSoftExit($signo): void
    {
        pcntl_signal($signo, SIG_IGN);
        $this->log('Soft finish by signal');
        $this->getApp()->getServer()->softFinish();
    }
}