<?php
/**
 * Base classenum for node types
 */
abstract class aNode extends aSpawnedFromEnum
{
    protected static $dbgLvl = Logger::DBG_NODE;

    /** @var string  */
    protected static $enumClass = 'NodeClassEnum'; /* overrided */

    /** @var bool  */
    protected $isClient = false;  /* can be overrided in client child */
    public function isClient() : bool {return $this->isClient;}

    /** @var int  */
    protected $canAccept = null;
    public function setCanAccept($val) : self {$this->canAccept = $val; return $this;}
    public function getCanAccept() : int {return $this->canAccept;}

    /** @var int  */
    protected $canConnect = null;
    public function setCanConnect($val) : self {$this->canConnect = $val; return $this;}
    public function getCanConnect() : int {return $this->canConnect;}

    public static function create(aBase $parent) : self
    {
        $me = new static($parent);

        $me
            ->setTypeFromEnum()
            ->setCanAccept(NodeClassEnum::getCanAccept($me->getType()))
            ->setCanConnect(NodeClassEnum::getCanConnect($me->getType()));

        $parent->dbg($me->getName() .  ' defined');

        return $me;
    }

    public static function spawn(aBase $parent, int $type) : self
    {
        if (static::$enumClass === null) {
            throw new Exception("Bad code - not defined enumClass");
        }

        /** @var aClassEnum $enumClass */
        $enumClass = static::$enumClass;

        /** @var self $className */
        if ($className = $enumClass::getClassName($type)) {
            return $className::create($parent);
        }

        throw new Exception("Bad code - cannot spawn class from $enumClass for type " . $type);
    }
}