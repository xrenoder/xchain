<?php


abstract class aSimpleAddressMessage extends aSimpleMessage
{
    /** @var int  */
    protected $maxLen = MessageFieldClassEnum::SIMPLE_ADDR_MAX_LEN;   /* overrided */

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(
        MessageFieldClassEnum::ADDR =>      'remoteAddrBin',
    );

    /** @var string  */
    protected $remoteAddrBin = null;
    public function getRemoteAddrBin() : string {return $this->remoteAddrBin;}

    /** @var Address  */
    protected $remoteAddress = null;
    public function setRemoteAddress(?Address $val) : self {$this->remoteAddress = $val; return $this;}
    public function getRemoteAddress() : ?Address {return $this->remoteAddress;}

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
        $body = $this->bodySimpleAddress();

        return $this->compileMessage($body);
    }

    protected function bodySimpleAddress() : string
    {
        $bodyParent = $this->bodySimple();

        $addrField = AddrMessageField::packField($this->getLocator()->getMyAddress()->getAddressBin());

        $this->signedData .= $addrField;

        return  $bodyParent . $addrField;
    }
}