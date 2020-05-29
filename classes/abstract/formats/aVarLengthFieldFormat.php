<?php


abstract class aVarLengthFieldFormat extends aFieldFormat
{
    public function packField($data) : string
    {
        return $this->packVariableLength($this->packDataTransform($data));
    }

    public function unpackField(string $data)
    {
        $this->unpackVariableLength($data);

        if ($this->raw !== null) {
            $this->unpackRawTransform();
        }

        return $this->value;
    }

    private function packVariableLength($data) : string
    {
        $length = strlen($data);

        if ($length > FieldFormatClassEnum::getMaxValue($this->lengthFormatId)) {
            throw new Exception("Bad length of " . $this->getName() . ": $length more than " . FieldFormatClassEnum::getMaxValue($this->lengthFormatId));
        }

        return pack($this->lengthFormatId, $length) . $data;
    }

    private function unpackVariableLength(string $data) : void
    {
        $lengthFormatLen = FieldFormatClassEnum::getLength($this->lengthFormatId);
        $rawLength = unpack($this->lengthFormatId, substr($data, $this->offset, $lengthFormatLen))[1];

        $this->raw = substr($data, $this->offset + $lengthFormatLen, $rawLength);
        $this->length = $rawLength + $lengthFormatLen;

        if (strlen($this->raw) < $rawLength) {
            $this->raw = null;
        }
    }
}