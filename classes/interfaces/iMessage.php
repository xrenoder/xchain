<?php
/**
 * Interface for messages classes
 */

interface iMessage extends iSpawned
{
    public static function create(Socket $socket) : ?aMessage;
    public static function createMessage() : string;
    public static function parser(Socket $socket, string $packet) : bool;
    public function getBufferSize() : int;
}