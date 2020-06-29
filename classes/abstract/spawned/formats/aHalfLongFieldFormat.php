<?php


class aHalfLongFieldFormat extends aFieldFormat
{
    protected function &packDataTransform(&$data) : string
    {
        $this->packDataCommon($data);

        $result = substr(pack(FieldFormatClassEnum::getPackFormat($this->type), $data), 1, 3);

        return $result;
   }

    protected function &unpackRawTransform()
    {
        $fourBytesRaw = hex2bin('00') . $this->rawWithoutLength;
        $this->value = unpack(FieldFormatClassEnum::getPackFormat($this->type), $fourBytesRaw)[1];

        $this->unpackRawCommon();

        return $this->value;
    }
}