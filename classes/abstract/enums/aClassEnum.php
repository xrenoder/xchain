<?php
/**
 * Base classenum for enumerations of classes (with checking pre-spawning tool)
 */
abstract class aClassEnum extends aEnum
{
    protected static $baseClassName = 'aBaseEnum'; /* override me */

    /**
     * @return string
     * @throws Exception
     */
    public static function getBaseClassName() : string
    {
        if (!static::$baseClassName) {
            throw new Exception(static::class . ' knows nothing about his base classenum name');
        }

        if (!is_a(static::$baseClassName, 'aBaseEnum', true)) {
            throw new Exception( static::$baseClassName . ' is not instance of aBaseEnum classenum');
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
            throw new Exception( "$className is not instance of $baseClassName classenum");
        }

        return $className;
    }

    public static function getIdByClassName(string $className)
    {
        $keys = array_keys(static::$items, $className);

        if (count($keys) !== 1) {
            return null;
        }

        return $keys[0];
    }
}