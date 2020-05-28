<?php


class DbField extends aField
{
    protected static $dbgLvl = Logger::DBG_DB_FLD;

    public function setName() : aField {$this->name = DbFieldClassEnum::getItem($this->id); return $this;}

    public static function spawn(aDbRow $row, int $id, int $offset) : self
    {
        /** @var DbField $className */

        if ($className = DbFieldClassEnum::getClassName($id)) {
            $field = $className::create($row, $offset);
            $field->setId($id);
            $field->fillMe($offset);

            return $field;
        }

        throw new Exception("Bad code - unknown DB field class for ID " . $id);
    }
}