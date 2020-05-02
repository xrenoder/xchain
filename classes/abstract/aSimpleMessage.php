<?php


abstract class aSimpleMessage extends aMessage
{
    /**
     * @return string
     */
    public static function createMessage(array $data) : string
    {
        $myNodeId = $data[static::DATA_MY_NODE_ID];
        $type = pack(MessFldEnum::getFormat(static::MESS_TYPE), static::$id);
        $node = pack(MessFldEnum::getFormat(static::MESS_NODE), $myNodeId);
        $len = strlen($type . $node) + MessFldEnum::getLength(static::MESS_LENGTH);
        $mess = $type . pack(MessFldEnum::getFormat(static::MESS_LENGTH), $len) . $node;

        return $mess;
    }

    abstract protected function incomingMessageHandler(): bool;
}