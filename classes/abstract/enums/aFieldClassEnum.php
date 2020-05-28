<?php


class aFieldClassEnum extends aClassEnum
{
    public static function getFormat(int $fieldId) : string
    {
        return static::$data[$fieldId];
    }

    public static function getLength(int $fieldId) : int
    {
        return FieldFormatClassEnum::getLength(static::$data[$fieldId]);
    }
}