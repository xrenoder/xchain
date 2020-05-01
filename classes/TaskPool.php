<?php


class TaskPool extends aBase
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

    /**
     * @param Queue $queue
     * @param string $name
     * @return self
     */
    public static function create(Queue $queue, string $name) : self
    {
        $me = new static($queue);

        $me->setName($name);

        return $me;
    }

    /**
     * @return self
     */
    public function toQueue() : self
    {
        if ($this->isAdded) {
            $this->dbg(static::$dbgLvl,$this->name . ' Pool already added to Queue');
            return $this;
        }

        $this->isAdded = true;
        $this->getQueue()->addPool($this);

        $this->dbg(static::$dbgLvl,$this->name . ' Pool added to Queue');

        return $this;
    }

    /**
     * @param iTask $task
     * @return bool
     */
    public function addTask(iTask $task) : bool
    {
        if ($this->isFinished) {
            $this->dbg(static::$dbgLvl,$this->name . ' Pool already finished, cannot add ' . $task->getName() . ' Task');
            return false;
        }

        if (!$this->isAdded) {      // can change pool priority only if pool not added to queue
            $priority = $task->getPriority();

            if ($this->priority === null || $priority > $this->priority) {
                $this->priority = $priority;
            }
        }

        $this->tasks[] = $task;
        $this->dbg(static::$dbgLvl,$task->getName() . ' Task added to ' . $this->name. ' Pool');

        if ($this->isRunned) {
            if ($task->run() === false) {
                return false;
            }

            $this->runnedTasks++;

//            $this->dbg(static::$dbgLvl,$this->name . ' Pool: ' . $this->runnedTasks . ' tasks was started');
        }

        return true;
    }

    /**
     * @return bool
     */
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
            if ($task->run() === false) {
                $result = false;
            } else {
                $this->runnedTasks++;
            }
        }

//        $this->dbg(static::$dbgLvl,$this->name . ' Pool: ' . $this->runnedTasks . ' tasks was started');

        return $result;
    }

    /**
     * @return self
     */
    public function finishTask() : self
    {
        $this->finishedTasks++;

        if ($this->finishedTasks && $this->finishedTasks == $this->runnedTasks) {
            $this->finish();
        }

        return $this;
    }

    /**
     * @return self
     */
    public function finish() : self
    {
        if ($this->handler) {
            $handler = $this->handler;
            $handler($this->data);
        }

        $this->isFinished = true;
        $this->dbg(static::$dbgLvl,$this->name . ' Pool finished (' . $this->finishedTasks . ' tasks)');

        return $this;
    }
}