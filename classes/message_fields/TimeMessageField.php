<?php


class TimeMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::MESS_FLD_TIME;  /* overrided */

    public function check(): bool
    {
        // Clients (type NodeClassEnum::CLIENT_ID) may have unsynchronized time
        if (
            $this->getMessage()->getRemoteNodeId() === NodeClassEnum::CLIENT_ID
            || $this->getMessage()->getSocket()->getMyNodeId() === NodeClassEnum::CLIENT_ID
        ) {
            return true;
        }

// TODO проверить ошибку времени при обмене сообщениями между неклиентскими нодами
        $time = time();
        $diff = abs($this->value - $time);

// TODO заменить 2 секунды разницы во времени между созданием запроса и приемом на константу или полученое из блокчейна правило
// возможно, локальное время нужно брать в момент получения запроса
        if ($diff >= 2) {
            $this->dbg("BAD TIME message time $this->value have too big different with local time $time for " . $this->getMessage()->getName());
            $this->getMessage()->setBadTime();
            return false;
        }

        return true;
    }
}