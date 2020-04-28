<?php
/**
 * Interface for messages classes
 */

interface iMessage
{
    public static function create(Socket $socket): aMessage;
    public static function spawn(Socket $socket, int $enumId): ?aMessage;
    public static function parser(Socket $socket, string $packet) : bool;
    public static function createMessage() : string;

}