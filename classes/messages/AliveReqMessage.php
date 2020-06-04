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

//AliveResMessage::create($this->getLocator(), [self::DATA => 'test', self::AUTHKEY => $myPubKey]);
        $response
            = AliveResMessage::create($this->getLocator())
                ->setData('test')
                ->setAuthorPublicKey($myPubKey);

        $legate->createResponseString($response);

        return self::MESSAGE_PARSED;
    }
}