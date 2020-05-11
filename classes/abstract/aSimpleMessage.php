<?php


abstract class aSimpleMessage extends aMessage
{
    /**
     * @return string
     */
    public function createMessageString() : string
    {
        $nodeField = NodeMessageField::packField($this->getSocket()->getMyNodeId());
        $timeField = TimeMessageField::packField(time());

        $body = $nodeField . $timeField;

        $typeField = TypeMessageField::packField(static::$id);
        $messageStringLength = strlen($typeField) + LengthMessageField::getLength() + strlen($body);
        $lenField = LengthMessageField::packField($messageStringLength);

        return $typeField . $lenField . $body;
    }
}