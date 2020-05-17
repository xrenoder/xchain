<?php


abstract class aSimpleMessage extends aMessage
{
    /** @var int  */
    protected $maxLen = MessageFieldClassEnum::SIMPLE_MAX_LEN;   /* overrided */

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(
        MessageFieldClassEnum::NODE =>      'remoteNodeId',
        MessageFieldClassEnum::TIME =>      'sendingTime',
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

    protected function __construct(aBase $parent)
    {
        parent::__construct($parent);
        $this->fields = array_replace($this->fields, self::$fieldSet);
    }

    /**
     * @return string
     */
    public function createMessageString(string $data = null) : string
    {
        $nodeField = NodeMessageField::packField($this->getSocket()->getMyNodeId());
        $timeField = TimeMessageField::packField(time());

        $body = $nodeField . $timeField;

        return $this->compileMessage($body);
    }
}