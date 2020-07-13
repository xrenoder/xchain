<?php


abstract class aSimpleMessage extends aMessage
{
    use tMessageConstructor;

    /* 'property' => '[fieldType, objectMethod or false]' or 'formatType' */
    protected static $fieldSet = array(
        'senderNode' => [MessageFieldClassEnum::NODE, false],
        'sendingTime' => [MessageFieldClassEnum::TIME, false],
    );

    /** @var int  */
    protected $senderNodeType = null;
    public function setSenderNodeType(int $val) : self {$this->senderNodeType = $val; return $this;}
    public function getSenderNodeType() : ?int {return $this->senderNodeType;}

    /** @var int  */
    protected $sendingTime = null;
    public function getSendingTime() : int {return $this->sendingTime;}

    /** @var int  */
    protected $myNodeType = null;
    public function setMyNodeType(int $val) : self {$this->myNodeType = $val; return $this;}
    public function getMyNodeType() : int {return $this->myNodeType;}

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
        $rawNode = NodeMessageField::pack($this, $this->getLocator()->getMyNodeType());
        $time = time();
        $rawTime = TimeMessageField::pack($this,$time);

        $this->raw = $rawNode . $rawTime;
        $this->signedData = $this->raw;
    }
}