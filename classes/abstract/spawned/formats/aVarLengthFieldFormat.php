<?php


abstract class aVarLengthFieldFormat extends aFieldFormat
{
    protected $fieldLength = null;

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

        if (FieldFormatClassEnum::getPackFormat($this->lengthFormatId) === null) {
            throw new Exception($this->getName() . " Bad code: format $this->lengthFormatId cannot be used as length of data");
        }

        $lengthFieldFormatObject = aFieldFormat::spawn($this->getField(), $this->lengthFormatId);
        return $lengthFieldFormatObject->packField($length) . $data;

//        return pack(FieldFormatClassEnum::getPackFormat($this->lengthFormatId), $length) . $data;
    }

    private function unpackVariableLength(string $data) : void
    {
        $lengthFormatLen = FieldFormatClassEnum::getLength($this->lengthFormatId);

        if ($this->fieldLength === null) {
            if (FieldFormatClassEnum::getPackFormat($this->lengthFormatId) === null) {
                throw new Exception($this->getName() . " Bad code: format $this->lengthFormatId cannot be used as length of data");
            }

            $this->rawFieldLength = substr($data, $this->offset, $lengthFormatLen);

            $lengthFieldFormatObject = aFieldFormat::spawn($this->getField(), $this->lengthFormatId);
            $this->fieldLength = $lengthFieldFormatObject->unpackField($this->rawFieldLength);
            unset($lengthFieldFormatObject);

            if ($this->fieldLength === null) {  // value of length more than max possible value of lengthFormat
                $this->rawWithoutLength = null;
                $this->length = null;
                $this->value = null;

                return;
            }

            $this->length = $this->fieldLength + $lengthFormatLen;
        }

        $this->rawWithoutLength = substr($data, $this->offset + $lengthFormatLen, $this->fieldLength);

        if (strlen($this->rawWithoutLength) < $this->fieldLength) {
            $this->rawWithoutLength = null;
        }
    }
}