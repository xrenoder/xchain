<?php


abstract class aAsIsVarLengthFieldFormat extends aVarLengthFieldFormat
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