<?php


abstract class aSimpleMessage extends aMessage
{
    /**
     * @return string
     */
    public static function createMessage(array $data) : string
    {
        $myNodeId = $data[static::DATA_MY_NODE_ID];

        $type = static::packField(static::MESS_TYPE, static::$id);
        $node = static::packField(static::MESS_NODE, $myNodeId);

        $lenLength = static::getLenLength() . strlen($type . $node);
        $len = static::packField(static::MESS_LENGTH, $lenLength);

        $mess = $type . $len . $node;

        return $mess;
    }

    abstract protected function incomingMessageHandler(): bool;
}