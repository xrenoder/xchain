<?php


abstract class aSimpleMessage extends aMessage
{
    use tMessageConstructor;

    /* 'property' => [fieldType, isObject] */
    protected static $fieldSet = array(
        'remoteNode' => [MessageFieldClassEnum::NODE, 'getType'],
        'sendingTime' => [MessageFieldClassEnum::TIME, false],
    );

    /** @var aNode  */
    private $remoteNode = null;
    public function setRemoteNode(aNode $val) : self {$this->remoteNode = $val; return $this;}
    public function getRemoteNode() : aNode {return $this->remoteNode;}

    /** @var int  */
    protected $sendingTime = null;
    public function getSendingTime() : int {return $this->sendingTime;}

    /** @var aNode  */
    private $myNode = null;
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