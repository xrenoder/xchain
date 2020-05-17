<?php


class DataMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::DATA;  /* overrided */

    public function check(): bool
    { return true; /* do nothing */ }
}