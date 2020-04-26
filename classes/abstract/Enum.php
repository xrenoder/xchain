<?php
/**
 * Enumeration base class
 */

abstract class Enum
{
    protected static $items = array(/* override me */);

    public static function getItem($id) {
        if (!isset(static::$items[$id])) {
            throw new Exception(static::class . ' knows nothing about ' . $id);
        }

        return static::$items[$id];
    }

    public static function isSet($id): bool
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