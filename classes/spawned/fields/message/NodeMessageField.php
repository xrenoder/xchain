<?php


class NodeMessageField extends aMessageField
{
    public function checkValue() : bool
    {
        if (!NodeEnum::isSetItem($this->getValue())) {
            $this->err($this->getName() . " parsing error: unknown node with type " . $this->getValue());
            $this->parsingError = true;
            return false;
        }

// check nodes compatiblity
        $legate = $this->getLegate();

        $myNodeType = $legate->getMyNodeType(); // use nodeId from legate (setted from task), not from locator, because locator node can be changed

        if ($legate->isConnected()) {
            $myCriteria = NodeEnum::getCanConnect($myNodeType);
            $logTxt = "cannot connect to";
        } else {
            $myCriteria = NodeEnum::getCanAccept($myNodeType);
            $logTxt = "cannot accept connection from";
        }

        if (($myCriteria & $this->getValue()) === 0)  {
            $this->err('Nodes incompatible: ' . NodeEnum::getName($myNodeType) . " $logTxt " . NodeEnum::getName($this->getValue()));
            $legate->setCloseAfterSend();
            $legate->createResponseString(BadNodeResMessage::create($this->getLocator()));
            return false;
        }

        return true;
    }

    public function postPrepare() :  bool
    {
        /** @var aSimpleMessage $message */
        $message = $this->getMessage();

        $message->setMyNodeType($this->getLegate()->getMyNodeType());
        $message->setSignedData($this->getRawWithLength());

        return true;
    }
}