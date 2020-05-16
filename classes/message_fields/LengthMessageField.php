<?php


class LengthMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::LENGTH;  /* overrided */

    public function check(): bool
    {
        $message = $this->getMessage();
        $maxLen = $message->getMaxLen();

        if ($maxLen && $this->value > $maxLen) {
            $this->dbg("BAD DATA declared length $this->value more than maximum $maxLen for " . $message->getName());
            return $this->getSocket()->badData();
        }

        if ($message->getLen() > $this->value) {
            $this->dbg("BAD DATA length " . $message->getLen() . " more than declared length $this->value for " . $message->getName() . " (2)");
            return $this->getSocket()->badData();
        }

        return true;
    }
}