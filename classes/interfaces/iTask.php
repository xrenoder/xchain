<?php
/**
 * Interface for task classes
 */
interface iTask
{
    public static function create(Server $server, TaskPool $pool, Host $host) : aTask;
    public function toPool() : aTask;
    public function run() : bool;
    public function finish() : void;
    public function getPriority() : ?int;
    public function getName() : string;
}