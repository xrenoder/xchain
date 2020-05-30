<?php


class DataMessageField extends aMessageField
{
    /** @var int  */
    protected $id = MessageFieldClassEnum::DATA;  /* overrided */

    public function check(): bool
    {
        $message = $this->getMessage();

        $message->setSignedData($this->getRawWithLength() . $message->getSignedData());

        return true;
    }
}