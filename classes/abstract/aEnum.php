<?php
/**
 * Base class for all enumerations
 */

abstract class aEnum implements iEnum
{
    protected static $items = array(/* override me */);

    public static function getItem($id) : string
    {
        if (!isset(static::$items[$id])) {
            throw new Exception(static::class . ' knows nothing about ' . $id);
        }

        return static::$items[$id];
    }

    public static function isSetItem($id): bool
    {
        if (isset(static::$items[$id])) {
            return true;
        }

        return false;
    }

    public static function getItemsList(): array
    {
        return static::$items;
    }
}