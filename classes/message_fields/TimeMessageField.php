<?php


class TimeMessageField extends aMessageField
{
    /** @var int  */
    protected static $id = MessageFieldClassEnum::MESS_FLD_TIME;  /* overrided */

    public function check(): bool
    {
        $time = time();
        $diff = abs($this->value - $time);

// TODO заменить 2 секунды разницы во времени между созданием запроса и приемом на константу или полученое из блокчейна правило
// возможно, локальное время нужно брать в момент получения запроса
        if ($diff >= 2) {
            $this->dbg("BAD DATA message time $this->value have big different with local time $time for " . $this->getMessage()->getName());
            return $this->getSocket()->badData();
        }

        return true;
    }
}