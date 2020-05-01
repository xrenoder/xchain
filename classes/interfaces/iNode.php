<?php
/**
 * Interface for node classes
 */

interface iNode extends iSpawned
{
    public static function create(App $app) : ?aNode;
}