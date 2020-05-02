<?php


class aNode extends aBase
{
    protected static $dbgLvl = Logger::DBG_NODE;

    /** @var int  */
    protected static $id;   /* override me */
    public function getId() : int {return static::$id;}
    /** @var string */
    protected static $name = 'NotDeclaredNodeName'; /* override me */
    public function getName() : string {return static::$name;}

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
        $app->dbg(static::$dbgLvl,static::$name .  ' Node defined');

        $me = new static($app);

        $data = NodeClassEnum::getData(static::$id);
        $me
            ->setCanAccept($data[NodeClassEnum::DATA_CAN_ACCEPT])
            ->setCanConnect($data[NodeClassEnum::DATA_CAN_CONNECT]);

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