<?php
/**
 * Interface for node classes
 */

interface iNode extends iSpawned
{
    public static function create(App $app) : ?aNode;
    public function getId() : int;
    public function getName() : string;
    public function getCanAccept() : int;
    public function getCanConnect() : int;
}