<?php


class AsIsFormat extends aFieldFormat
{
    protected function packDataTransform($data) : string
    {
        return $data;
    }

    protected function unpackRawTransform()
    {
        $this->value = $this->rawWithoutLength;
        return $this->value;
    }
}