<?php


class AsIsFormat extends aFieldFormat
{
    /** @var string  */
    protected $id = FieldFormatClassEnum::ASIS;  /* overrided */

    protected function packDataTransform($data) : string
    {
        return $data;
    }

    protected function unpackRawTransform()
    {
        $this->value = $this->raw;
        return $this->value;
    }
}