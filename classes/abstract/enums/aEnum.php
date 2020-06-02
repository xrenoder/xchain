<?php
/**
 * Base classenum for all enumerations
 */

abstract class aEnum
{
    protected static $items = array(/* override me */);
    protected static $data = array(/* override me */);

    /**
     * @param $id
     * @return string
     * @throws Exception
     */
    public static function getItem($id) : string
    {
        if (!isset(static::$items[$id])) {
            throw new Exception(static::class . ' knows nothing about ' . $id);
        }

        return static::$items[$id];
    }

    /**
     * @param $id
     * @return array
     * @throws Exception
     */
    public static function getData($id) : array
    {
        if (!isset(static::$data[$id])) {
            throw new Exception(static::class . ' knows nothing about data of ' . $id);
        }

        return static::$data[$id];
    }

    /**
     * @param $id
     * @return bool
     */
    public static function isSetItem($id) : bool
    {
        if (isset(static::$items[$id])) {
            return true;
        }

        return false;
    }

    /**
     * @param $id
     * @return bool
     */
    public static function isSetData($id) : bool
    {
        if (isset(static::$data[$id])) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public static function getItemsList() : array
    {
        return static::$items;
    }
}