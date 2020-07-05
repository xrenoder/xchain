<?php


class TimeMessageField extends aMessageField
{
    public function checkValue(): bool
    {
        /* @var aSimpleMessage $message */
        $message = $this->getMessage();

// Clients (type NodeClassEnum::CLIENT_ID) may have unsynchronized time
        if (
            $message->getSenderNode()->getType() === NodeClassEnum::CLIENT
            || $message->getMyNode()->getType() === NodeClassEnum::CLIENT
        ) {
            return true;
        }

// TODO проверить ошибку времени при обмене сообщениями между неклиентскими нодами
        $diff = abs($this->getValue() - $message->getIncomingMessageTime());

// TODO заменить 2 секунды разницы во времени между созданием запроса и приемом на константу или полученое из блокчейна правило
        if ($diff >= 2) {
            $this->dbg($this->getName() . " BAD TIME message time " . $this->getValue() . " have too big different with local incoming message time " . $message->getIncomingMessageTime() . " for " . $message->getName());
            $legate = $this->getLegate();
            $legate->setCloseAfterSend();
            $legate->createResponseString(BadTimeResMessage::create($this->getLocator()));
            return false;
        }

        return true;
    }
}