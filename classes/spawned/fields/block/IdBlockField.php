<?php


class IdBlockField extends aBlockField
{
    public function postPrepare(): bool
    {
        $this->getBlock()->setSignedData($this->getRawWithLength());

        return true;
    }
}