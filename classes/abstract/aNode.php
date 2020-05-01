<?php


class aNode extends aBase implements iNode
{
    protected static $dbgLvl = Logger::DBG_NODE;

    /** @var int  */
    protected static $enumId;   /* override me */
    /** @var string */
    protected static $name = 'NotDeclaredNodeName'; /* override me */

    public static function create(App $app) : self
    {
        $app->dbg(static::$dbgLvl,static::$name .  ' Node defined');

        $me = new static($app);

        return $me;
    }

    public static function spawn(aBase $app, int $enumId) : ?aBase
    {
        /** @var aNode $className */

        if ($className = NodeClassEnum::getClassName($enumId)) {
            return $className::create($app);
        }

        return null;
    }
}