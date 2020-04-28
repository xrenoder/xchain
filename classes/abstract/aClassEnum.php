<?php
/**
 * Base class for enumerations of classes (with checking pre-spawning tool)
 */

class aClassEnum extends aEnum implements iClassEnum
{
    protected static $baseClassName = '';

    public static function getBaseClassName() : string
    {
        if (!static::$baseClassName) {
            throw new Exception(static::class . ' knows nothing about his base class name');
        }

        if (!is_a(static::$baseClassName, 'aBaseApp', true)) {
            throw new Exception( static::$baseClassName . ' is not instance of AppBase class');
        }

        return static::$baseClassName;
    }

    public static function getClassName($enumId) : ?string
    {
        if (!MessageEnum::isSetItem($enumId)) {
            return null;
        }

        $className = MessageEnum::getItem($enumId);
        $baseClassName = MessageEnum::getBaseClassName();

        if (!is_a($className, $baseClassName, true)) {
            throw new Exception( "$className is not instance of $baseClassName class");
        }

        return $className;
    }
}