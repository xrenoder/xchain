<?php


class BinHexFormat extends aFieldFormat
{
    /** @var string  */
    protected $id = FieldFormatClassEnum::BINHEX;  /* overrided */

    protected function packDataTransform($data) : string
    {
        return hex2bin($data);
    }

    protected function unpackRawTransform()
    {
        $this->value = bin2hex($this->raw);
        return $this->value;
    }
}