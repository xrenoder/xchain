<?php
/**
 * Base class for node types
 */
abstract class aNode extends aBaseEnum
{
    protected static $dbgLvl = Logger::DBG_NODE;

    /** @var string  */
    protected $enumClass = 'NodeClassEnum'; /* overrided */

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

    public static function create(aLocator $locator) : self
    {
        $me = new static($locator);

        /** @var aClassEnum $enumClass */
        $enumClass = $me->getEnumClass();

        if (($id = $enumClass::getIdByClassName(get_class($me))) === null) {
            throw new Exception("Bad code - unknown ID (not found or not exclusive) for field class " . $me->getName());
        }

        $me
            ->setId($id)
            ->setCanAccept(NodeClassEnum::getCanAccept($id))
            ->setCanConnect(NodeClassEnum::getCanConnect($id));

        $locator->dbg($me->getName() .  ' defined');

        return $me;
    }

    public static function spawn(aLocator $locator, int $id) : aNode
    {
        /** @var aNode $className */

        if ($className = NodeClassEnum::getClassName($id)) {
            return $className::create($locator);
        }

        throw new Exception("Bad code - unknown node class for ID " . $id);
    }
}