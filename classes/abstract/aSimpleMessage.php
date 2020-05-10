<?php


abstract class aSimpleMessage extends aMessage
{
    /**
     * @return string
     */
    public static function createMessage(array $data) : string
    {
        $myNodeId = $data[static::MY_NODE_ID];

// TODO вставлять время в момент начала отправки запроса, посколько более приоритетные запросы могут отложить выполнение других и те окажутся просрочены
        $time = TimeMessageField::packField(time());
        $node = NodeMessageField::packField($myNodeId);
        $body = $time . $node;

        $type = TypeMessageField::packField(static::$id);
        $lenLength = MessageFieldClassEnum::getLenLength();
        $messLength = $lenLength + strlen($type . $body);
        $len = LengthMessageField::packField($messLength);

        return $type . $len . $body;
    }

//    abstract protected function incomingMessageHandler(): bool;
}