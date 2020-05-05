<?php
/**
 * Base class for node types
 */
class aNode extends aBase
{
    protected static $dbgLvl = Logger::DBG_NODE;

    /** @var int  */
    protected static $id;   /* override me */
    public function getId() : int {return static::$id;}
    /** @var string */
    protected $name;
    public function setName() : self {$this->name = NodeClassEnum::getItem(static::$id); return $this;}
    public function getName() : string {return $this->name;}

    /** @var int  */
    protected $canAccept = null;
    public function setCanAccept($val) : self {$this->canAccept = $val; return $this;}
    public function getCanAccept() : int {return $this->canAccept;}

    /** @var int  */
    protected $canConnect = null;
    public function setCanConnect($val) : self {$this->canConnect = $val; return $this;}
    public function getCanConnect() : int {return $this->canConnect;}

    public static function create(App $app) : self
    {
        $me = new static($app);

        $data = NodeClassEnum::getData(static::$id);

        $me
            ->setName()
            ->setCanAccept($data[NodeClassEnum::DATA_CAN_ACCEPT])
            ->setCanConnect($data[NodeClassEnum::DATA_CAN_CONNECT]);

        $app->dbg($me->getName() .  ' defined');

        return $me;
    }

    public static function spawn(App $app, int $id) : ?aNode
    {
        /** @var aNode $className */

        if ($className = NodeClassEnum::getClassName($id)) {
            return $className::create($app);
        }

        return null;
    }
}