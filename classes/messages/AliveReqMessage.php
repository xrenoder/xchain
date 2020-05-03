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
        $socket = $this->getSocket();
        if (!$socket->areNodesCompatible()) {
            $socket->addOutData(BadNodeResMessage::createMessage(array(static::DATA_MY_NODE_ID => $this->getApp()->getMyNode()->getId())));
            $socket->setCloseAfterSend();
        } else if ($socket->isServerBusy()) {
            $socket->addOutData(BusyResMessage::createMessage(array(static::DATA_MY_NODE_ID => $this->getApp()->getMyNode()->getId())));
            $socket->setCloseAfterSend();
        } else {
            $socket->addOutData(AliveResMessage::createMessage(array(static::DATA_MY_NODE_ID => $this->getApp()->getMyNode()->getId())));
            $socket->setAliveChecked();
            $socket->cleanMessage();
        }

//        $this->getSocket()->setFreeAfterSend();
        return false;
    }
}