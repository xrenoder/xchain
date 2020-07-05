<?php


class SignerBlockField extends aBlockField
{
    /** @var bool  */
    protected $isPubKeySetFromMessage = false;
    public function setIsPubKeySetFromMessage(bool $val) : self {$this->isPubKeySetFromMessage = $val; return $this;}
    public function isPubKeySetFromMessage() : bool {return $this->isPubKeySetFromMessage;}

    public function checkValue() : bool
    {
        if (!Address::checkAddressBin($this->getValue())) {
            $this->err($this->getName() . " BAD DATA address is bad " . Address::binToBase16($this->getValue()));
            $this->parsingError = true;
            return false;
        }

        $block = $this->getBlock();

        if ($block->getParent() instanceof aSignedMessage) {
            /** @var aSignedMessage $message */
            $message = $block->getParent();

            if ($message->getAuthorAddress()->getAddressBin() !== $this->getValue()) {
                $this->err($this->getName() . " BAD DATA message author address " . $message->getAuthorAddress()->getAddressHuman() . " and transaction author address " . Address::binToBase16($this->getValue()) . " are different ");
                $this->parsingError = true;
                return false;
            }

            $this->setIsPubKeySetFromMessage(true);
        }

        if (!$block->isAlreadySaved()) {
            $signerNode = NodeByAddrDbRow::create($this, $this->getValue());

            if ($signerNode->getNode() === null) {
                $this->err($this->getName() . " BAD DATA don't know about node with block signer address " . Address::binToBase16($this->getValue()));
                $this->parsingError = true;
                return false;
            }

            if ($signerNode->getNode()->getType() !== $block->getChain()->getSignerNode()->getType()) {
                $this->err($this->getName() . " BAD DATA block signer address " . Address::binToBase16($this->getValue()) . " not valid node type " . $block->getChain()->getSignerNode()->getType());
                $this->parsingError = true;
                return false;
            }
        }

        return true;
    }

    public function setObject() : void
    {
        if ($this->isPubKeySetFromMessage()) {
            $this->object = $this->getBlock()->getParent()->getAuthorAddress();
        } else {
            $this->object = PubKeyByAddrDbRow::create($this->getLocator(), $this->getValue())->getAddressWithPubKey();
        }
    }

    public function checkObject(): bool
    {
        /** @var Address $signerAddress */
        $signerAddress = $this->object;

        if ($signerAddress === null) {
            $this->err($this->getName() . " BAD DATA don't know public key for signer address " . Address::binToBase16($this->getValue()));
            $this->parsingError = true;
            return false;
        }

        if ($signerAddress->isAddressOnly()) {
            $this->err($this->getName() . " BAD DATA block signature cannot be verified - need signer public key for" . $signerAddress->getAddressHuman());
            $this->parsingError = true;
            return false;
        }

        return true;
    }

    public function postPrepare() :  bool
    {
        $block = $this->getBlock();
        $block->addSignedData($this->getRawWithLength());
        $block->addSignedData($block->getChain()->getLastPreparedBlockSignature());

        return true;
    }
}