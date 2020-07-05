<?php


class NodeMessageField extends aMessageField
{
    public function checkValue() : bool
    {
        /** @var NodeClassEnum $enumClass */
        $enumClass = aNode::getStaticEnumClass();

        if (!$enumClass::isSetItem($this->getValue())) {
            $this->err($this->getName() . " parsing error: object cannot be created from type " . $this->getValue());
            $this->parsingError = true;
            return false;
        }

        return true;
    }

    public function setObject() : void
    {
        $this->object = aNode::spawn($this->getMessage(), $this->getValue());
    }

    public function checkObject() : bool
    {
        /* @var aSimpleMessage $message */
        $message = $this->getMessage();
        $legate = $this->getLegate();

// check nodes compatiblity
// TODO посмотреть, возможно ли заранее создать объект myNode в локаторе
        $myNode = aNode::spawn($this, $legate->getMyNodeType()); // use nodeId from legate (setted from task), not from locator, because locator node can be changed

        if ($legate->isConnected()) {
            $myCriteria = $myNode->getCanConnect();
            $logTxt = "cannot connect to";
        } else {
            $myCriteria = $myNode->getCanAccept();
            $logTxt = "cannot accept connection from";
        }

        if ($myCriteria & $this->getValue() === 0)  {
            /** @var aNode $senderNode */
            $senderNode = $this->object;

            $this->err('Nodes uncompatible: ' . $myNode->getName() . " $logTxt " . $senderNode->getName());
            $legate->setCloseAfterSend();
            $legate->createResponseString(BadNodeResMessage::create($this->getLocator()));
            return false;
        }

        $message->setMyNode($myNode);

        return true;
    }

    public function postPrepare() :  bool
    {
        $this->getMessage()->setSignedData($this->getRawWithLength());

        return true;
    }
}