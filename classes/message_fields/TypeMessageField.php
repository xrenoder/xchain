<?php


class TypeMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::TYPE;  /* overrided */

    public function check(): bool
    {
        return true;
    }
}