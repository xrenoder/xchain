<?php


class AddrMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::ADDR;  /* overrided */

    public function check(): bool
    {
        /* @var aSimpleAddressMessage $message */
        $message = $this->getMessage();
        $socket = $this->getSocket();
        $addr = $message->getRemoteAddrBin();

        if (!Address::checkAddressBin($addr)) {
            $this->dbg("BAD DATA address is bad " . Address::binToBase16($addr));
            return $this->getSocket()->badData();
        }

        $socketAddr = $socket->getRemoteAddrBin();

        if ($socketAddr !== null) {
            if ($socketAddr !== $addr) {
                $this->dbg("BAD DATA received address " . Address::binToBase16($addr) . " is different than recieved before " . Address::binToBase16($socketAddr));
                return $this->getSocket()->badData();
            }
        } else {
// TODO добавить проверку в блокчейне, может ли отправитель сообщения исполнять роль той ноды, которой представляется

            $socket->setRemoteAddrBin($addr);
        }

        $this->dbg("Message received from address " . Address::binToBase16($addr));

        return true;
    }
}