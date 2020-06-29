<?php
/**
 * Request "Is daemon alive?"
 */
class AliveReqMessage extends aSimpleAddressMessage
{
    /**
     * @return bool
     */
    protected function incomingMessageHandler() : bool
    {
        $legate = $this->getLegate();

        $legate->setCloseAfterSend();

        $response = AliveResMessage::create($this->getLocator());

        $legate->createResponseString($response);

        return self::MESSAGE_PARSED;
    }
}