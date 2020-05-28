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
    public function getData() : string {return $this->data;}

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
        $body = $this->bodyData();

        return $this->compositeMessage($body);
    }

    protected function bodyData() : string
    {
        if (empty($this->outgoingString) || !isset($this->outgoingString[self::DATA])) {
            $data = '';
        } else {
            $data = $this->outgoingString[static::DATA];
        }

        $bodyParent = $this->bodySimpleAddress();

        $dataField = DataMessageField::pack($this,$data);

        $this->signedData = $dataField . $this->signedData;

        return $bodyParent . $dataField;
    }
}