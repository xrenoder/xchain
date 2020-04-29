<?php


class TaskPool extends aBaseApp
{
    protected static $dbgLvl = Logger::DBG_POOL;

    public function getQueue() : Queue {return $this->getParent();}
    public function getServer() : Server {return $this->getQueue()->getServer();}

    /** @var iTask[]  */
    private $tasks = array();

    /** @var array  */
    private $data = array();
    public function setData($val, $key) : self {$this->data[$key] = $val; return $this;}
    public function getData($key) {return $this->data[$key] ?? null;}

    /** @var callable  */
    private $handler = null;
    public function setHandler($val) : self {$this->handler = $val; return $this;}

    /** @var string */
    private $name;
    public function setName($val) : self {$this->name = $val; return $this;}

    /** @var int */
    private $priority = null;
    public function setPriority($val) : self {$this->priority = $val; return $this;}
    public function getPriority() : ?int {return $this->priority;}

    /** @var bool  */
    private $isAdded = false;

    /** @var bool  */
    private $isRunned = false;

    /** @var bool  */
    private $isFinished = false;

    /** @var int  */
    private $runnedTasks = 0;

    /** @var int  */
    private $finishedTasks = 0;

    public static function create(Queue $queue, string $name) : self
    {
        $me = new static($queue);

        $me->setName($name);

        return $me;
    }

    public function toQueue() : self
    {
        if ($this->isAdded) {
            $this->dbg(static::$dbgLvl,$this->name . ' Pool already added to Queue');
            return $this;
        }

        $this->isAdded = true;
        $this->getQueue()->addPool($this);

        return $this;
    }

    public function addTask(iTask $task) : bool
    {
        if ($this->isFinished) {
            $this->dbg(static::$dbgLvl,$this->name . ' Pool already finished, cannot add ' . $task::getName() . ' Task');
            return false;
        }

        if (!$this->isAdded) {
            $priority = $task->getPriority();
            if ($this->priority === null || $priority > $this->priority) {
                $this->priority = $priority;
            }
        }

        $this->tasks[] = $task;
        $this->dbg(static::$dbgLvl,$this->name . ' Pool started');

        if ($this->isRunned) {
            if (!$task->run()) {
                return false;
            }

            $this->runnedTasks++;
        }

        return true;
    }

    public function run() : bool
    {
        if ($this->isFinished) {
            $this->dbg(static::$dbgLvl,$this->name . ' Pool already finished, cannot start');
            return false;
        }

        if ($this->isRunned) {
            $this->dbg(static::$dbgLvl,$this->name . ' Pool already started, cannot start');
            return false;
        }

        $this->isRunned = true;
        $this->dbg(static::$dbgLvl,$this->name . ' Pool started');

        $result = true;

        foreach ($this->tasks as $task) {
            if (!$task->run()) {
                $result = false;
            } else {
                $this->runnedTasks++;
            }
        }

        return $result;
    }

    public function finishTask() {
        $this->finishedTasks++;

        if ($this->finishedTasks == $this->runnedTasks) {
            $this->finish();
        }
    }

    public function finish()
    {
        if ($this->handler) {
            $handler = $this->handler;
            $handler($this->data);
        }

        $this->isFinished = true;
        $this->dbg(static::$dbgLvl,$this->name . ' Pool finished');
    }
}