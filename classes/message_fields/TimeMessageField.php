<?php


class TimeMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::TIME;  /* overrided */

    public function check(): bool
    {
        /* @var aSimpleMessage $message */
        $message = $this->getMessage();
        $legate = $this->getLegate();

// Clients (type NodeClassEnum::CLIENT_ID) may have unsynchronized time
        if (
            $message->getRemoteNodeId() === NodeClassEnum::CLIENT_ID
            || $legate->getMyNodeId() === NodeClassEnum::CLIENT_ID
        ) {
            return true;
        }

// TODO проверить ошибку времени при обмене сообщениями между неклиентскими нодами
        $diff = abs($this->value - $message->getIncomingMessageTime());

// TODO заменить 2 секунды разницы во времени между созданием запроса и приемом на константу или полученое из блокчейна правило
        if ($diff >= 2) {
            $this->dbg("BAD TIME message time $this->value have too big different with local incoming message time " . $message->getIncomingMessageTime() . " for " . $message->getName());
            $legate->setCloseAfterSend();
            $legate->createResponseString(BadTimeResMessage::create($this->getLocator()));
            return false;
        }

        $message->setSignedData($message->getSignedData() . $this->raw);

        return true;
    }
}