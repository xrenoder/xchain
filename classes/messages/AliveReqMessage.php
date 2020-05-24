<?php
/**
 * Request "Is daemon alive?"
 */
class AliveReqMessage extends aSimpleAddressMessage
{
    /** @var int  */
    protected static $id = MessageClassEnum::ALIVE_REQ; /* overrided */

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