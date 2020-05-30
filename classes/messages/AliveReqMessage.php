<?php
/**
 * Request "Is daemon alive?"
 */
class AliveReqMessage extends aSignMessage
{
    /**
     * @return bool
     */
    protected function incomingMessageHandler() : bool
    {
        $legate = $this->getLegate();

        $legate->setCloseAfterSend();

        $myPubKey = $this->getLocator()->getMyAddress()->getPublicKeyBin();

        $legate->createResponseString(AliveResMessage::create($this->getLocator(), [self::DATA => 'test', self::AUTHKEY => $myPubKey]));

        return self::MESSAGE_PARSED;
    }
}