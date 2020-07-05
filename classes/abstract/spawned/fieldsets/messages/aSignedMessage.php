<?php


abstract class aSignedMessage extends aDataMessage
{
    use tMessageConstructor;

    /* 'property' => [fieldType, isObject] */
    protected static $fieldSet = array(
        'signature' => [MessageFieldClassEnum::SIGN, false],
    );

    /** @var string  */
    protected $signature = null;
    public function getSignature() : string {return $this->signature;}

    public function createRaw() : aFieldSet
    {
        $this->rawSignedMessage();

        $this->compositeRaw();

        return $this;
    }

    protected function rawSignedMessage() : void
    {
        $this->dbg($this->getName() . " signed data: " . bin2hex($this->signedData));
        $rawSignature = SignMessageField::pack($this,$this->getLocator()->getMyAddress()->signBin($this->signedData));

        $this->rawDataMessage();

        $this->raw .= $rawSignature;
    }
}