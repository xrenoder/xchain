<?php


class aHalfLongFieldFormat extends aFieldFormat
{
    protected function packDataTransform($data) : string
    {
        $this->packDataCommon($data);

        $result = pack(FieldFormatClassEnum::getPackFormat($this->id), $data);

        return substr($result, 1, 3);
   }

    protected function unpackRawTransform()
    {
        $fourBytesRaw = hex2bin('00') . $this->rawWithoutLength;
        $this->value = unpack(FieldFormatClassEnum::getPackFormat($this->id), $fourBytesRaw)[1];

        return $this->unpackRawCommon();
    }
}