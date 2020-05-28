<?php


class BinHexLongFormat extends aVarLengthFieldFormat
{
    /** @var string  */
    protected $id = FieldFormatClassEnum::BINHEX_LBE;  /* overrided */

    /** @var string  */
    protected $lengthFormatId = FieldFormatClassEnum::ULONG_BE;  /* overrided */

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