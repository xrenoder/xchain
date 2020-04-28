<?php
/**
 * Interface for task classes
 */

interface iTask
{
    public static function create(Queue $queue): aTask;
    public function queue() : aTask;
    public function run() : bool;
}