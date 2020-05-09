<?php


abstract class aSimpleMessage extends aMessage
{
    /**
     * @return string
     */
    public static function createMessage(array $data) : string
    {
        $myNodeId = $data[static::MY_NODE_ID];

        $type = TypeMessageField::packField(static::$id);
        $node = NodeMessageField::packField($myNodeId);

        $lenLength = MessageFieldClassEnum::getLenLength();
        $messLength = $lenLength + strlen($type . $node);
        $len = LengthMessageField::packField($messLength);

        $mess = $type . $len . $node;

        return $mess;
    }

    abstract protected function incomingMessageHandler(): bool;
}