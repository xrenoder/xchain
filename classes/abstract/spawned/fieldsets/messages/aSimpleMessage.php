<?php


abstract class aSimpleMessage extends aMessage
{
    use tMessageConstructor;

    /* 'property' => [fieldType, isObject] */
    protected static $fieldSet = array(
        'senderNode' => [MessageFieldClassEnum::NODE, 'getType'],
        'sendingTime' => [MessageFieldClassEnum::TIME, false],
    );

    /** @var aNode  */
    protected $senderNode = null;
    public function setSenderNode(aNode $val) : self {$this->senderNode = $val; return $this;}
    public function getSenderNode() : aNode {return $this->senderNode;}

    /** @var int  */
    protected $sendingTime = null;
    public function getSendingTime() : int {return $this->sendingTime;}

    /** @var aNode  */
    protected $myNode = null;
    public function setMyNode(aNode $val) : self {$this->myNode = $val; return $this;}
    public function getMyNode() : aNode {return $this->myNode;}

    public function setMaxLen() : aMessage
    {
        $this->maxLen = MessageFieldClassEnum::getSimpleMaxLen();

        return $this;
    }

    public function createRaw() : aFieldSet
    {
        $this->rawSimpleMessage();

        $this->compositeRaw();

        return $this;
    }

    protected function rawSimpleMessage() : void
    {
        $rawNode = NodeMessageField::pack($this, $this->getLocator()->getMyNode()->getType());
        $time = time();
        $rawTime = TimeMessageField::pack($this,$time);

        $this->raw = $rawNode . $rawTime;
        $this->signedData = $this->raw;
    }
}