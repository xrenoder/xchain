<?php


class AuthorTransactionField extends aTransactionField
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

        $transaction = $this->getTransaction();

        if ($transaction->getParent() instanceof aSignedMessage) {
            /** @var aSignedMessage $message */
            $message = $transaction->getParent();

            if ($message->getAuthorAddress()->getAddressBin() !== $this->getValue()) {
                $this->err($this->getName() . " BAD DATA message author address " . $message->getAuthorAddress()->getAddressHuman() . " and transaction author address " . Address::binToBase16($this->getValue()) . " are different ");
                $this->parsingError = true;
                return false;
            }

            $this->setIsPubKeySetFromMessage(true);
        }

        return true;
    }

    public function setObject() : void
    {
        if ($this->isPubKeySetFromMessage()) {
            $this->object = $this->getTransaction()->getParent()->getAuthorAddress();
        } else {
            $this->object = PubKeyByAddrDbRow::create($this->getLocator(), $this->getValue())->getAddressWithPubKey();
        }
    }

    public function checkObject(): bool
    {
        /** @var Address $authorAddress */
        $authorAddress = $this->object;

        if ($authorAddress === null) {
            $this->err($this->getName() . " BAD DATA don't know public key for transaction author address " . Address::binToBase16($this->getValue()));
            $this->parsingError = true;
            return false;
        }

        if ($authorAddress->isAddressOnly()) {
            $this->err($this->getName() . " BAD DATA transaction signature cannot be verified - need author public key for" . $authorAddress->getAddressHuman());
            $this->parsingError = true;
            return false;
        }

        return true;
    }
}