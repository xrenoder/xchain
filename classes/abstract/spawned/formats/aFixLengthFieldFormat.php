<?php


abstract class aFixLengthFieldFormat extends aFieldFormat
{
    public function packField($data) : string
    {
        $data = $this->packDataTransform($data);

        if ($this->length && strlen($data) !== $this->length) {
            $length = strlen($data);
            throw new Exception("Bad pack length of " . $this->getName() . ": $length not equal " . $this->length);
        }

        return $data;
    }

    public function unpackField(string $data)
    {
        $this->rawWithoutLength = substr($data, $this->offset, $this->length);

        if ($this->length && strlen($this->rawWithoutLength) !== $this->length) { // length of data not equal data length described in format
            $this->dbg("Bad unpack length of " . $this->getName() . ": " . strlen($this->rawWithoutLength) . " not equal " . $this->length);

            $this->rawWithoutLength = null;
            $this->length = null;
            $this->value = null;

            return $this->value;
        }

        $this->unpackRawTransform();

        return $this->value;
    }
}