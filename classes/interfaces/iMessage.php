<?php
/**
 * Interface for messages classes
 */

interface iMessage
{
    public static function create(Socket $socket): aMessage;
    public static function spawn(Socket $socket, int $enumId): ?aMessage;

    public static function createMessage() : string;

    public static function getLengthLen() : int;
    public static function getSpawnLen() : int;
    public static function getLength(string $data) : int;
    public static function getType(string $data) : int;
}