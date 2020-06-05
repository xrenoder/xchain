<?php
/**
 * Base classenum for enumerations of classes (with checking pre-spawning tool)
 */
abstract class aClassEnum extends aEnum
{
    protected static $baseClassName = 'aSpawnedFromEnum'; /* override me */

    /**
     * @return string
     * @throws Exception
     */
    public static function getBaseClassName() : string
    {
        if (!is_a(static::$baseClassName, 'aSpawnedFromEnum', true)) {
            throw new Exception( static::$baseClassName . ' is not instance of aSpawnedFromEnum');
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
            throw new Exception( "$className is not instance of $baseClassName");
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