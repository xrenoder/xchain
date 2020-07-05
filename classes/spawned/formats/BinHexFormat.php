<?php


class BinHexFormat extends aFieldFormat
{
    protected function &packDataTransform(&$data) : string
    {
        $result = hex2bin($data);
        return $result;
    }

    protected function &unpackRawTransform()
    {
        $this->value = bin2hex($this->rawWithoutLength);
        return $this->value;
    }
}