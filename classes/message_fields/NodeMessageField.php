<?php


class NodeMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::NODE;  /* overrided */

    public function check(): bool
    {
        /* @var aSimpleMessage $message */
        $message = $this->getMessage();
        $legate = $this->getLegate();

        $message->setRemoteNode(aNode::spawn($this->getLocator(), $message->getRemoteNodeId()));

// check nodes compatiblity
        $myNodeId = $legate->getMyNodeId(); // use nodeId from legate (setted from task), not from locator, because locator node can be changed

        if ($legate->isConnected()) {
            $myCriteria = NodeClassEnum::getCanConnect($myNodeId);
            $logTxt = "cannot connect to";
        } else {
            $myCriteria = NodeClassEnum::getCanAccept($myNodeId);
            $logTxt = "cannot accept connection from";
        }

        if ($myCriteria & $message->getRemoteNodeId() === 0)  {
            $this->dbg('Nodes uncompatible: ' . NodeClassEnum::getName($myNodeId) . " $logTxt " . NodeClassEnum::getName($message->getRemoteNodeId()));
            $legate->setCloseAfterSend();
            $legate->createResponse(BadNodeResMessage::create($this->getLocator()));
            return false;
        }

        $message->setSignedData($this->raw);

        return true;
    }
}