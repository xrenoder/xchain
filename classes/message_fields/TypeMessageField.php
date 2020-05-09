<?php


class TypeMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::MESS_FLD_TYPE;  /* overrided */

    public function check(): bool
    {/* do nothing */ return true;}
}