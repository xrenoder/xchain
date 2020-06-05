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

        if ($this->rawWithoutLength !== null) {
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

        return pack(FieldFormatClassEnum::getPackFormat($this->lengthFormatId), $length) . $data;
    }

    private function unpackVariableLength(string $data) : void
    {
        $lengthFormatLen = FieldFormatClassEnum::getLength($this->lengthFormatId);
        $this->rawFieldLength = substr($data, $this->offset, $lengthFormatLen);
        $fieldLength = unpack(FieldFormatClassEnum::getPackFormat($this->lengthFormatId), $this->rawFieldLength)[1];

        $maxLength = FieldFormatClassEnum::getMaxValue($this->lengthFormatId);

        if ($maxLength && $fieldLength > $maxLength) {  // value of length more than max possible value of lengthFormat
            $this->dbg("Bad unpack length of " . $this->getName() . ": $fieldLength more than $maxLength");

            $this->rawWithoutLength = null;
            $this->length = null;
            $this->value = null;

            return;
        }

        $this->rawWithoutLength = substr($data, $this->offset + $lengthFormatLen, $fieldLength);
        $this->length = $fieldLength + $lengthFormatLen;

        if (strlen($this->rawWithoutLength) < $fieldLength) {
            $this->rawWithoutLength = null;
        }
    }
}