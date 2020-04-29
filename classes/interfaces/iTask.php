<?php
/**
 * Interface for task classes
 */
interface iTask
{
    public static function create(Server $server, TaskPool $pool, Host $host): iTask;
    public function toPool() : iTask;
    public function run() : bool;
    public function finish();
    public function getPriority() : ?int;
    public function getName() : string;
}