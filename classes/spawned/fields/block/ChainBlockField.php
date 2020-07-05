<?php


class ChainBlockField extends aBlockField
{
    public function setObject(): void
    {
        $block = $this->getBlock();
        $block->dbTransBegin();
        $this->object = ChainByIdDbRow::create($block, $this->getValue());
    }

    public function checkObject(): bool
    {
        /** @var ChainByIdDbRow $chain */
        $chain = $this->object;
        $lastPreparedBlockId = $chain->getLastPreparedBlockId();

        if ($lastPreparedBlockId === null) {
            $this->err($this->getName() . " parsing error: chain data not saved for " . $this->getValue());
            $this->parsingError = true;
            return false;
        }

        $block = $this->getBlock();
        $blockId = $block->getId();

        if ($blockId !== 0) {
            if ($lastPreparedBlockId >= $blockId) {
                $block->setAlreadySaved(true);
                $block->dbTransRollback();
            } else if (($lastPreparedBlockId + 1) !== $blockId) {
                $block->setPrepareLater(true);
                $block->dbTransRollback();
            }
        } else {
            if ($chain->getLastPreparedBlockSignature() !== '') {
                $block->setAlreadySaved(true);
                $block->dbTransRollback();
            }
        }

        return true;
    }

    public function postPrepare() :  bool
    {
        $block = $this->getBlock();

        if ($block->isPrepareLater()) {
            $block->saveRawForLaterPreparing();
            $this->parsingError = true;
            return false;
        }

        $block->addSignedData($this->getRawWithLength());

        return true;
    }
}