<?php


class LengthMessageField extends aMessageField
{
    /** @var int  */
    protected $id = MessageFieldClassEnum::LENGTH;  /* overrided */

    public function check(): bool
    {
        $message = $this->getMessage();
        $legate = $this->getLegate();
        $maxLen = $message->getMaxLen();
        $messageLen = $message->getRawStringLen();
        $declaredLen = $this->getValue();

        if ($maxLen && $declaredLen > $maxLen) {
            $this->dbg("BAD DATA declared length $declaredLen more than maximum $maxLen for " . $message->getName());
            $legate->setBadData();
            return false;
        }

        if ($messageLen > $declaredLen) {
            $this->dbg("BAD DATA length $messageLen more than declared length $declaredLen for " . $message->getName() . " (2)");
            $legate->setBadData();
            return false;
        }

        $legate->setReadBufferSize($declaredLen - $messageLen + 1);

        return true;
    }
}