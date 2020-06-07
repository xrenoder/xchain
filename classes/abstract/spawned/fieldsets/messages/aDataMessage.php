<?php


abstract class aDataMessage extends aSimpleAddressMessage
{
    /** @var int  */
    protected $maxLen = MessageFieldClassEnum::UNKNOWN_LEN;   /* overrided */

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(
        MessageFieldClassEnum::DATA =>      'data',
    );

    /** @var string  */
    protected $data = null;
    public function setData(string $val) : self {$this->data = $val; return $this;}
    public function getData() : string {return $this->data;}

    protected function __construct(aBase $parent)
    {
        parent::__construct($parent);
        $this->fields = array_replace($this->fields, self::$fieldSet);
    }

    /**
     * @return string
     */
    public function createRaw() : string
    {
        $this->rawDataMessage();

        return $this->compositeRaw();
    }

    protected function rawDataMessage() : void
    {
        if ($this->data === null) {
            $this->data = '';
        }

        $this->rawSimpleAddressMessage();

        $rawData = DataMessageField::pack($this,$this->data);

        $this->signedData = $rawData . $this->signedData;

        $this->raw .= $rawData;
    }
}