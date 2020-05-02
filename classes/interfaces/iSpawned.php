<?php
/**
 * Interface for spawned (from enumeration, by id) classes
 */
interface iSpawned extends iBase
{
    public static function spawn(aBase $parent, int $id) : ?aBase;
}