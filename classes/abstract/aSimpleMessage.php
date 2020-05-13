<?php


abstract class aSimpleMessage extends aMessage
{
    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fields = array(
        MessageFieldClassEnum::MESS_FLD_TYPE =>      '',                // must be always first field in message
        MessageFieldClassEnum::MESS_FLD_LENGTH =>    'declaredLen',     // must be always second field in message
        MessageFieldClassEnum::MESS_FLD_NODE =>      'remoteNodeId',
        MessageFieldClassEnum::MESS_FLD_TIME =>      'sendingTime',
    );

    /** @var int  */
    protected $remoteNodeId = null;
    public function getRemoteNodeId() : int {return $this->remoteNodeId;}

    /** @var int  */
    protected $sendingTime = null;
    public function getSendingTime() : int {return $this->sendingTime;}

    /** @var bool  */
    protected $isBadTime = false;
    public function setBadTime() : self {$this->isBadTime = true; return $this;}
    public function isBadTime() : bool {return $this->isBadTime;}

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