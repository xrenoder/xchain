<?php


class AuthorTransactionField extends aTransactionField
{
    public function checkValue() : bool
    {
        if (!Address::checkAddressBin($this->getValue())) {
            $this->err($this->getName() . " BAD DATA address is bad " . Address::binToBase16($this->getValue()));
            $this->parsingError = true;
            return false;
        }

        $transaction = $this->getTransaction();

        if ($transaction->getParent() instanceof aAuthorPublicKeyMessage) {
            /** @var aAuthorPublicKeyMessage $message */
            $message = $transaction->getParent();

            if ($message->getAuthorAddress()->getAddressBin() !== $this->getValue()) {
                $this->err($this->getName() . " BAD DATA message author address " . $message->getAuthorAddress()->getAddressHuman() . " and transaction author address " . Address::binToBase16($this->getValue()) . " are different ");
                $this->parsingError = true;
                return false;
            }

            $transaction->setIsPubKeySetFromMessage(true);
        }

        return true;
    }

    public function setObject() : void
    {
        $transaction = $this->getTransaction();

        if ($transaction->isPubKeySetFromMessage()) {
            $this->object = $transaction->getParent()->getAuthorAddress();
        } else {
            $this->object = PubKeyByAddr::create($this->getLocator(), $this->getValue())->getAddressWithPubKey();
        }
    }

    public function checkObject(): bool
    {
        if ($this->object === null) {
            $this->err($this->getName() . " BAD DATA don't know public key for author address " . Address::binToBase16($this->getValue()));
            $this->parsingError = true;
            return false;
        }

        return true;
    }
}