<?php


abstract class aFixLengthFieldFormat extends aFieldFormat
{
    public function packField($data) : string
    {
        $data = $this->packDataTransform($data);

        if (strlen($data) !== $this->length) {
            $length = strlen($data);
            throw new Exception("Bad pack length of " . $this->getName() . ": $length not equal " . $this->length);
        }

        return $data;
    }

    public function unpackField(string $data)
    {
        $this->rawWithoutLength = substr($data, $this->offset, $this->length);

        if (strlen($this->rawWithoutLength) !== $this->length) {
            throw new Exception("Bad unpack length of " . $this->getName() . ": " . strlen($this->rawWithoutLength) . " not equal " . $this->length);
        }

        $this->unpackRawTransform();

        return $this->value;
    }
}