<?php


abstract class aFixLengthFieldFormat extends aFieldFormat
{
    public function &packField(&$data) : string
    {
        if ($data === null) {
            throw new Exception($this->getName() . " Bad coding: packed data must be not null");
        }

        $result = $this->packDataTransform($data);

        if ($this->length && strlen($result) !== $this->length) {
            $length = strlen($result);
            throw new Exception("Bad pack length of " . $this->getName() . ": $length not equal " . $this->length);
        }

        return $result;
    }

    public function &unpackField(string &$raw)
    {
        $fieldRaw = substr($raw, $this->offset, $this->length);

        if ($this->length && strlen($fieldRaw) !== $this->length) { // length of data not equal data length described in format
            $this->dbg("Bad unpack length of " . $this->getName() . ": " . strlen($fieldRaw) . " not equal " . $this->length);

            $this->unsetRaw();
            $this->length = null;
            $this->value = null;

            return $this->value;
        }

        $this->setRaw($fieldRaw);

        $this->unpackRawTransform();

        return $this->value;
    }
}