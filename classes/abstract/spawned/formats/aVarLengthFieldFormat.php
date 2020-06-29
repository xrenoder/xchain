<?php


abstract class aVarLengthFieldFormat extends aFieldFormat
{
    protected $fieldLength = null;

    public function &packField(&$data) : string
    {
        if ($data === null) {
            throw new Exception($this->getName() . " Bad coding: packed data must be not null");
        }

        return $this->packVariableLength($this->packDataTransform($data));
    }

    public function &unpackField(string &$fieldRaw)
    {
        $this->unpackVariableLength($fieldRaw);

        if ($this->rawWithoutLength !== null) {
            $this->unpackRawTransform();
        }

        return $this->value;
    }

    private function &packVariableLength(&$data) : string
    {
        $length = strlen($data);

        if (FieldFormatClassEnum::getPackFormat($this->lengthFormatType) === null) {
            throw new Exception($this->getName() . " Bad code: format $this->lengthFormatType cannot be used as length of data");
        }

        $result = $this->simplePack($this->lengthFormatType, $length) . $data;

        return $result;
    }

    private function unpackVariableLength(string &$data) : void
    {
        $lengthFormatLen = FieldFormatClassEnum::getLength($this->lengthFormatType);

        if ($this->fieldLength === null) {
            if (FieldFormatClassEnum::getPackFormat($this->lengthFormatType) === null) {
                throw new Exception($this->getName() . " Bad code: format $this->lengthFormatType cannot be used as length of data");
            }

            $this->rawFieldLength = substr($data, $this->offset, $lengthFormatLen);

            $this->fieldLength = $this->simpleUnpack($this->lengthFormatType, $this->rawFieldLength);

            if ($this->fieldLength === null) {  // value of length more than max possible value of lengthFormat
                $this->unsetRaw();
                $this->length = null;
                $this->value = null;

                return;
            }

            $this->length = $this->fieldLength + $lengthFormatLen;
        }

        $fieldRaw = substr($data, $this->offset + $lengthFormatLen, $this->fieldLength);

        if (strlen($fieldRaw) < $this->fieldLength) {
            $this->unsetRaw();
        } else {
            $this->setRaw($fieldRaw);
        }
    }
}