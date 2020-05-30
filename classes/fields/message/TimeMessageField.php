<?php


class TimeMessageField extends aMessageField
{
    /** @var int  */
    protected $id = MessageFieldClassEnum::TIME;  /* overrided */

    public function check(): bool
    {
        /* @var aSimpleMessage $message */
        $message = $this->getMessage();
        $legate = $this->getLegate();

        $message->setSignedData($message->getSignedData() . $this->getRawWithLength());

// Clients (type NodeClassEnum::CLIENT_ID) may have unsynchronized time
        if (
            $message->getRemoteNodeId() === NodeClassEnum::CLIENT_ID
            || $legate->getMyNodeId() === NodeClassEnum::CLIENT_ID
        ) {
            return true;
        }

// TODO проверить ошибку времени при обмене сообщениями между неклиентскими нодами
        $diff = abs($this->getValue() - $message->getIncomingMessageTime());

// TODO заменить 2 секунды разницы во времени между созданием запроса и приемом на константу или полученое из блокчейна правило
        if ($diff >= 2) {
            $this->dbg("BAD TIME message time " . $this->getValue() . " have too big different with local incoming message time " . $message->getIncomingMessageTime() . " for " . $message->getName());
            $legate->setCloseAfterSend();
            $legate->createResponseString(BadTimeResMessage::create($this->getLocator()));
            return false;
        }

        return true;
    }
}