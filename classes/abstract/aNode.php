<?php
/**
 * Base class for node types
 */
abstract class aNode extends aBase
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

    /** @var bool  */
    protected $isClient = false;  /* can be overrided in client child */
    public function isClient() : bool {return $this->isClient;}

    public static function create(aLocator $locator) : self
    {
        $me = new static($locator);

        $data = NodeClassEnum::getData(static::$id);

        $me
            ->setName()
            ->setCanAccept($data[NodeClassEnum::CAN_ACCEPT])
            ->setCanConnect($data[NodeClassEnum::CAN_CONNECT]);

        $locator->dbg($me->getName() .  ' defined');

        return $me;
    }

    public static function spawn(aLocator $locator, int $id) : ?aNode
    {
        /** @var aNode $className */

        if ($className = NodeClassEnum::getClassName($id)) {
            return $className::create($locator);
        }

        return null;
    }
}