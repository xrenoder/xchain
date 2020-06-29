<?php


abstract class aFieldClassEnum extends aClassEnum
{
    public static function getFormat(int $fieldType) : string
    {
        return static::$data[$fieldType];
    }

    public static function getLength(int $fieldType) : int
    {
        return FieldFormatClassEnum::getLength(static::$data[$fieldType]);
    }
}