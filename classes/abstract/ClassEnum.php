<?php
/**
 * Enumeration of classes with checking pre-spawning tool
 */

class ClassEnum extends Enum
{
    protected static $baseClassName = '';

    public static function getBaseClassName() {
        if (!static::$baseClassName) {
            throw new Exception(__CLASS__ . ' knows nothing about his base class name');
        }

        if (!is_a(static::$baseClassName, 'AppBase', true)) {
            throw new Exception( static::$baseClassName . ' is not instance of AppBase class');
        }

        return static::$baseClassName;
    }

    public static function getClassName($enumId) {
        if (!MessageEnum::isSet($enumId)) {
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