<?php
/**
 * Interface for messages classes
 */

interface iMessage
{
    public static function create(Socket $socket): iMessage;
    public static function spawn(Socket $socket, int $enumId): ?iMessage;
    public static function parser(Socket $socket, string $packet) : bool;
    public static function createMessage() : string;

}