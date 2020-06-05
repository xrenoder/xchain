<?php


abstract class aSignMessage extends aAuthorPublicKeyMessage
{
    /** @var int  */
    protected $maxLen = MessageFieldClassEnum::UNKNOWN_LEN;   /* overrided */

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(
        MessageFieldClassEnum::SIGN =>      'signature',
    );

    /** @var string  */
    protected $signature = null;
    public function getSignature() : string {return $this->signature;}

    protected function __construct(aBase $parent)
    {
        parent::__construct($parent);
        $this->fields = array_replace($this->fields, self::$fieldSet);
    }

    /**
     * @return string
     */
    public function createMessageString() : string
    {
        $body = $this->bodySign();

        return $this->compositeMessage($body);
    }

    protected function bodySign() : string
    {
        $bodyParent = $this->bodyAuthorPublicKey();

        $this->dbg($this->getName() . " signed data: " . bin2hex($this->signedData));

        $signField = SignMessageField::pack($this,$this->getLocator()->getMyAddress()->signBin($this->signedData));

        return $bodyParent . $signField;
    }
}