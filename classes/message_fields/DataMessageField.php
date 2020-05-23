<?php


class DataMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::DATA;  /* overrided */

    public function check(): bool
    {
        $message = $this->getMessage();
        $message->setSignedData($this->raw . $message->getSignedData());
        return true;
    }
}