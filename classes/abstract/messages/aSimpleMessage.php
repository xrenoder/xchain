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

    /** @var aNode  */
    private $remoteNode = null;
    public function setRemoteNode(?aNode $val) : self {$this->remoteNode = $val; return $this;}
    public function getRemoteNode() : ?aNode {return $this->remoteNode;}

    /** @var int  */
    protected $sendingTime = null;
    public function getSendingTime() : int {return $this->sendingTime;}

    protected function __construct(aBase $parent)
    {
        parent::__construct($parent);
        $this->fields = array_replace($this->fields, self::$fieldSet);
    }

    /**
     * @return string
     */
    public function createMessageString() : string
    {
        $body = $this->bodySimple();

        return $this->compileMessage($body);
    }

    protected function bodySimple() : string
    {
        $myNodeId = $this->getLocator()->getMyNodeId();
        $time = time();

        $nodeField = NodeMessageField::packField($myNodeId);
        $timeField = TimeMessageField::packField($time);

        $this->signedData = $nodeField . $timeField;

        return $nodeField . $timeField;
    }
}