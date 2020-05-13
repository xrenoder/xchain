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
        if ($socket->areNodesCompatible() === false) {
            $socket->sendMessage(BadNodeResMessage::create($socket, []));
            $socket->setCloseAfterSend();
        } else if ($this->isBadTime()) {
            $socket->sendMessage(BadTimeResMessage::create($socket, []));
            $socket->setCloseAfterSend();
        } else {
            $socket->sendMessage(AliveResMessage::create($socket, []));
            $socket->setAliveChecked();
            $socket->cleanInMessage();
        }

//        $this->getSocket()->setFreeAfterSend();
        return false;
    }
}