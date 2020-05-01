<?php
/**
 * Interface of base application classes
 */
interface iBase
{
    public function log(string $message): void;
    public function err(string $message): void;
    public function dbg(int $lvl, string $message): void;
}