<?php


class LengthMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::MESS_FLD_LENGTH;  /* overrided */

    public function check(): bool
    {
        if ($this->getMessage()->getMaxLen() && $this->value > $this->getMessage()->getMaxLen()) {
            $this->dbg("BAD DATA declared length $this->value more than maximum " . $this->getMessage()->getMaxLen() . "for " . $this->getMessage()->getName());
            return $this->getSocket()->badData();
        }

        if ($this->getMessage()->getLen() > $this->value) {
            $this->dbg("BAD DATA length " . $this->getMessage()->getLen() . " more than declared length $this->value for " . $this->getMessage()->getName() . " (2)");
            return $this->getSocket()->badData();
        }

        return true;
    }
}