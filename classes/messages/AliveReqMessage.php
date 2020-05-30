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
        $legate->createResponseString(AliveResMessage::create($this->getLocator()));

        return self::MESSAGE_PARSED;
    }
}