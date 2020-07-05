<?php


class TimeBlockField extends aBlockField
{
    public function checkValue(): bool
    {
        $block = $this->getBlock();

        if (!$block->isAlreadySaved() && $this->getValue() <= $block->getChain()->getLastPreparedBlockTime()) {
            $this->err($this->getName() . " parsing error: last block time is less or equal time of parsed block");
            $this->parsingError = true;
            return false;
        }

        return true;
    }
}