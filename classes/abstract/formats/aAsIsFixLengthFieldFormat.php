<?php


abstract class aAsIsFixLengthFieldFormat extends aFixLengthFieldFormat
{
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