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
            ->setIdFromEnum()
            ->setCanAccept(NodeClassEnum::getCanAccept($me->getId()))
            ->setCanConnect(NodeClassEnum::getCanConnect($me->getId()));

        $parent->dbg($me->getName() .  ' defined');

        return $me;
    }

    public static function spawn(aBase $parent, $id) : self
    {
        if (static::$enumClass === null) {
            throw new Exception("Bad code - not defined enumClass");
        }

        /** @var aClassEnum $enumClass */
        $enumClass = static::$enumClass;

        /** @var aNode $className */
        if ($className = $enumClass::getClassName($id)) {
            return $className::create($parent);
        }

        throw new Exception("Bad code - cannot spawn class from $enumClass for ID " . $id);
    }
}