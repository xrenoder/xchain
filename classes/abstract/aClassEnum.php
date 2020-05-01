<?php
/**
 * Base class for enumerations of classes (with checking pre-spawning tool)
 */

class aClassEnum extends aEnum implements iClassEnum
{
    protected static $baseClassName = '';

    /**
     * @return string
     * @throws Exception
     */
    public static function getBaseClassName() : string
    {
        if (!static::$baseClassName) {
            throw new Exception(static::class . ' knows nothing about his base class name');
        }

        if (!is_a(static::$baseClassName, 'aBase', true)) {
            throw new Exception( static::$baseClassName . ' is not instance of AppBase class');
        }

        return static::$baseClassName;
    }

    /**
     * @param $id
     * @return string|null
     * @throws Exception
     */
    public static function getClassName($id) : ?string
    {
        if (!static::isSetItem($id)) {
            return null;
        }

        $className = static::getItem($id);
        $baseClassName = static::getBaseClassName();

        if (!is_a($className, $baseClassName, true)) {
            throw new Exception( "$className is not instance of $baseClassName class");
        }

        return $className;
    }
}