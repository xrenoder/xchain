<?php
/**
 * Interface for all enumerations
 */

interface iEnum
{
    public static function getItem($id) : string;
    public static function isSetItem($id): bool;
    public static function getItemsList(): array;
    public static function getData($id) : array;
}