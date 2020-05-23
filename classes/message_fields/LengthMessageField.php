<?php


class LengthMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::LENGTH;  /* overrided */

    public function check(): bool
    {
        $message = $this->getMessage();
        $legate = $this->getLegate();
        $maxLen = $message->getMaxLen();
        $messageLen = $message->getLen();

        if ($maxLen && $this->value > $maxLen) {
            $this->dbg("BAD DATA declared length $this->value more than maximum $maxLen for " . $message->getName());
            $legate->setBadData();
            return false;
        }

        if ($messageLen > $this->value) {
            $this->dbg("BAD DATA length $messageLen more than declared length $this->value for " . $message->getName() . " (2)");
            $legate->setBadData();
            return false;
        }

        $legate->setReadBufferSize($this->value - $messageLen + 1);

        return true;
    }
}