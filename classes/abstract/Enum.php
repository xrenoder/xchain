<?php
/**
 * Enumeration base class
 */

abstract class Enum
{
    protected static $items = array(/* override me */);

    public static function getItem($id) {
        if (!isset(self::$items[$id])) {
            throw new Exception(__CLASS__ . ' knows nothing about ' . $id);
        }

        return self::$items[$id];
    }

    public static function isSet($id): bool
    {
        if (isset(self::$items[$id])) {
            return true;
        }

        return false;
    }

    public static function getItemsList(): array
    {
        return self::$items;
    }
}