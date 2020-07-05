<?php


class LengthMessageField extends aMessageField
{
    public function checkValue(): bool
    {
        $message = $this->getMessage();
        $maxLen = $message->getMaxLen();
        $messageLen = $message->getRawLength();
        $declaredLen = $this->getValue();

        if ($maxLen && $declaredLen > $maxLen) {
            $this->err($this->getName() . " BAD DATA declared length $declaredLen more than maximum $maxLen for " . $message->getName());
            $this->parsingError = true;
            return false;
        }

        if ($messageLen > $declaredLen) {
            $this->err($this->getName() . " BAD DATA length $messageLen more than declared length $declaredLen for " . $message->getName() . " (2)");
            $this->parsingError = true;
            return false;
        }

        return true;
    }

    public function postPrepare() :  bool
    {
        $this->getLegate()->setReadBufferSize($this->getValue() - $this->getMessage()->getRawLength() + 1);

        return true;
    }
}