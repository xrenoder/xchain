<?php
use parallel\{Channel,Runtime,Events,Events\Event};
/**
 * Daemonization
 */
class Daemon extends aBase
{
    protected static $dbgLvl = Logger::DBG_DAEMON;

    public function getApp() : App {return $this->getParent();}

    /** @var string */
    private $pidFile;
    public function setPidFile($val) : self {$this->pidFile = $val; return $this;}

    /** @var string */
    private $runPath;
    public function setRunPath($val) : self {$this->runPath = $val; return $this;}

    private const CMD_RESTART = 'restart';				// command to force daemon restart
    private const CMD_STOP = 'stop';					// command to force daemon stop
    private const PS_COMMAND = 'ps fuwww -p';
    private const KILL_TIMEOUT = KILL_TIMEOUT;

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
     * @return self
     */
    public static function create(App $app, string $runPath, string $pidName) : self
    {
        $me = new self($app);

        $me->setRunPath($runPath);
        $me->setPidFile($runPath . $pidName);

        $app->setDaemon($me);

        $me->dbg("Daemon created");

        return $me;
    }

    public function run(string $command = null) : bool
    {
// daemonization ...
        if (pcntl_fork()) {
// ... and finich undaemonized copy
            $this->dbg("Undaemonized script finished");
            exit(0);
        }

        $this->dbg("New daemon started");

// set pid of new daemon to App
        $pid = posix_getpid();
        $this->getApp()->setPid($pid);

// unmount console and standard IO channels
        posix_setsid();
        chdir('/');

        fclose(STDIN);
        fopen('/dev/null', 'rb');
        fclose(STDOUT);
        fopen('/dev/null', 'ab');
        fclose(STDERR);
        fopen('/dev/null', 'ab');

        $this->dbg("Unmount suss");

// start workers threads
        $this->startWorkers();

        $this->dbg("Continue after workers starting");

// check pid-file
        $fd = fopen($this->pidFile, 'c+b');
        flock($fd, LOCK_EX);			// lock file for prevent concurrency
        $oldPid = fread($fd, 32);
        fseek($fd, 0);

// if daemon was started before ...
        if ($oldPid) {
// ... and no have STOP- or RESTART-command - check, is old daemon still alive
            if ($command !== static::CMD_RESTART && $command !== static::CMD_STOP) {
                $this->dbg("Check old daemon");

                if ($this->getApp()->getServer()->isDaemonAlive()) {
// exit, if old daemon is alive
                    flock($fd, LOCK_UN);
                    fclose($fd);
                    $this->dbg("Old daemon is alive ($oldPid)");
                    $this->dbg("New daemon finished");
                    exit(0);
                }

                $this->dbg("Continue after checking old daemon");
            }

// kill old daemon, if have STOP- or RESTART-command
            $this->log("Old daemon will be killed (pid $oldPid)");
            $this->kill($oldPid);
        }

        $this->dbg("Continue after pid checking");

        if ($command === static::CMD_STOP) {
            ftruncate($fd, 0);
            flock($fd, LOCK_UN);
            fclose($fd);
            $this->dbg("New daemon finished by STOP-command");
            exit(0);
        }

// set signal handlers
        foreach(static::$signals as $signal => $handler) {
            pcntl_signal($signal, array($this, $handler));
        }

        pcntl_async_signals(true);

// TODO сделать обработку сигналов в воркерах

// write new daemon pid to file and unlock them - we are ready for working
        ftruncate($fd, 0);
        fwrite($fd, $pid);
        flock($fd, LOCK_UN);
        fclose($fd);

        $this->log("Daemon started (pid $pid) and ready for working");
        return true;
    }

    private function startWorkers()
    {
        $workerThread = function (string $threadId, parallel\Channel $channelRecv, parallel\Channel $channelSend, int $debugMode)
        {
            $worker = new Worker($threadId);

// create logger-object
            Logger::create(
                $worker,
                $debugMode,
                LOG_PATH,
                LOG_EXT,
                LOG_FILE,
                SCRIPT_ERROR_LOG_FILE,
                PHP_ERROR_LOG_FILE
            );

            try {
// set DBA
                DBA::create($worker, DBA_HANDLER, DATA_PATH, DBA_EXT, DBA_LOCK_FILE, LOCK_EXT);
// load node private key
                $worker->setMyAddress(Address::createFromWallet($worker, MY_ADDRESS, WALLET_PATH));
                $worker->run($channelRecv, $channelSend);
            } catch (Exception $e) {
                throw new Exception($e->getMessage() . "\n" . var_export($e->getTraceAsString(), true));
            }

            return true;
        };

        $app = $this->getApp();
        $app->setEvents(new parallel\Events());
        $app->getEvents()->setBlocking(false); // Comment to block on Events::poll()
//    $app->getEvents()->setTimeout(1000000); // Uncomment when blocking

        $future = array();

        for($i = 1; $i <= THREADS_COUNT; $i++) {
            $threadId = "thread_" . $i;

            $app->setChannelFromSocket($threadId, new parallel\Channel(Channel::Infinite));
            $app->setChannelFromWorker($threadId, parallel\Channel::make($threadId, parallel\Channel::Infinite));
            $app->setThread($threadId,new parallel\Runtime(XCHAIN_PATH . "local.inc"));

            $future[] = $app->getThread($threadId)->run(
                $workerThread,
                [
                    $threadId,
                    $app->getChannelFromSocket($threadId),
                    $app->getChannelFromWorker($threadId),
                    $app->getLogger()->getDbgMode()
                ]
            );

            $app->getEvents()->addChannel($app->getChannelFromWorker($threadId));

            $this->dbg("Runtime $threadId runned");
        }

        sleep(1);

        $this->dbg("All " . THREADS_COUNT . " workers are started");
    }

    /**
     * Kill daemon with signals: SIGHUP, SIGTERM, SIGKILL
     * @param int $pid
     */
    private function kill(int $pid) : void
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
     * @param string $command
     * @param int $normalExitStatus
     * @return string
     */
    private function commandExec(string $command, int $normalExitStatus) : string
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
     * @param int $signo
     */
    public function signalHardExit($signo) : void
    {
        pcntl_signal($signo, SIG_IGN);
        $this->log("Hard finish by signal $signo");
        $this->getApp()->getServer()->hardFinish();
    }

    /**
     * Signal SIGHUP handler
     * @param int $signo
     */
    public function signalSoftExit(int $signo) : void
    {
        pcntl_signal($signo, SIG_IGN);
        $this->log('Soft finish by signal');
        $this->getApp()->getServer()->softFinish();
    }
}