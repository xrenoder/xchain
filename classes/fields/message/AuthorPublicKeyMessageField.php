<?php


class AuthorPublicKeyMessageField extends aMessageField
{
    /** @var int  */
    protected $id = MessageFieldClassEnum::PUBKEY;  /* overrided */

    public function check(): bool
    {
        /* @var aSignMessage $message */
        $message = $this->getMessage();
        $legate = $this->getLegate();
        $authorPublicKey = $message->getAuthorPublicKey();

        $message->setSignedData($this->getRawWithLength() . $message->getSignedData());

        if (strlen($authorPublicKey) !== Address::PUBLIC_BIN_LEN) {
            $this->dbg("BAD DATA bad public key length: " . strlen($authorPublicKey));
            $legate->setBadData();
            return false;
        }

        if ($message->getRemoteAddress() === null) {
            $remoteAddrBin = $message->getRemoteAddrBin();
            $remoteAddress = Address::createFromPublic($this->getLocator(), $message->getAuthorPublicKey());

            if ($remoteAddress->getAddressBin() !== $remoteAddrBin) {
                $this->dbg("BAD DATA public key for " . $remoteAddress->getAddressHuman() . " cannot be used with address " . Address::binToBase16($remoteAddrBin));
                $legate->setBadData();
                return false;
            }

            $message->setRemoteAddress($remoteAddress);
        }

        return true;
    }
}