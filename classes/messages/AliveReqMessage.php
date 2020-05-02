<?php
/**
 * Request "Is daemon alive?"
 */
class AliveReqMessage extends aSimpleMessage
{
    /** @var int  */
    protected static $id = MessageClassEnum::ALIVE_REQ; /* overrided */
    protected static $needAliveCheck = false;           /* overrided */

    /**
     * @return bool
     */
    protected function incomingMessageHandler() : bool
    {
        if (!$this->getSocket()->areNodesCompatible()) {
            $this->getSocket()->addOutData(BadNodeResMessage::createMessage(array(static::DATA_MY_NODE_ID => $this->getApp()->getMyNode()->getId())));
            $this->getSocket()->setCloseAfterSend();
        } else if ($this->getSocket()->isServerBusy()) {
            $this->getSocket()->addOutData(BusyResMessage::createMessage(array(static::DATA_MY_NODE_ID => $this->getApp()->getMyNode()->getId())));
            $this->getSocket()->setCloseAfterSend();
        } else {
            $this->getSocket()->addOutData(AliveResMessage::createMessage(array(static::DATA_MY_NODE_ID => $this->getApp()->getMyNode()->getId())));
            $this->getSocket()->setAliveChecked();
            $this->getSocket()->cleanMessage();
        }

//        $this->getSocket()->setFreeAfterSend();
        return false;
    }
}