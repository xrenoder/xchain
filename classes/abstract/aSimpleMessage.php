<?php


abstract class aSimpleMessage extends aMessage
{
    /**
     * @return string
     */
    public function createMessageString() : string
    {
        $myNodeId = $this->getSocket()->getMyNodeId();

        $nodeField = NodeMessageField::packField($myNodeId);
        $timeField = TimeMessageField::packField(time());
        $body = $nodeField . $timeField;

        $typeField = TypeMessageField::packField(static::$id);
        $messageStringLength = strlen($typeField) + LengthMessageField::getLength() + strlen($body);
        $lenField = LengthMessageField::packField($messageStringLength);

        return $typeField . $lenField . $body;
    }
}