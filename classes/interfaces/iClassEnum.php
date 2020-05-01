<?php
/**
 * Interface for enumerations of classes
 */

interface iClassEnum extends iEnum
{
    public static function getBaseClassName() : string;
    public static function getClassName($id) : ?string;
}