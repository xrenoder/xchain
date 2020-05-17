<?php


abstract class aDataSignMessage extends aSimpleAddressMessage
{
    /** @var int  */
    protected $maxLen = MessageFieldClassEnum::UNKNOWN_LEN;   /* overrided */

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */

    protected static $fieldSet = array(
//        MessageFieldClassEnum::TYPE =>      '',                // must be always first field in message
//        MessageFieldClassEnum::LENGTH =>    'declaredLen',     // must be always second field in message
//        MessageFieldClassEnum::NODE =>      'remoteNodeId',
//        MessageFieldClassEnum::TIME =>      'sendingTime',
//        MessageFieldClassEnum::ADDR =>      'remoteAddrBin',
        MessageFieldClassEnum::DATA =>      'data',
        MessageFieldClassEnum::SIGN =>      'signature',
    );

    /** @var string  */
    protected $data = null;
    public function getData() : string {return $this->data;}

    /** @var string  */
    protected $signature = null;
    public function getSignature() : string {return $this->signature;}

    protected function __construct(aBase $parent)
    {
        parent::__construct($parent);
        $this->fields = array_replace($this->fields, self::$fieldSet);
        $this->dbg("\n" . __CLASS__ . " fields:\n" . var_export($this->fields, true) . "\n");
    }

    /**
     * @return string
     */
    public function createMessageString(string $data = null) : string
    {
        if ($data === null) {
            throw new Exception("Bad coding: data must be not null here!!!");
        }

        $socket = $this->getSocket();
        $myAddress = $this->getApp()->getMyAddr();

        $myNodeId = $socket->getMyNodeId();
        $time = time();
        $myAddrBin = $myAddress->getAddressBin();

        $nodeField = NodeMessageField::packField($myNodeId);
        $timeField = TimeMessageField::packField($time);
        $addrField = AddrMessageField::packField($myAddrBin);
        $dataField = DataMessageField::packField($data);

        $signedData = $myNodeId . $myAddrBin . $time . $data;
        $signField = SignMessageField::packField($myAddress->signBin($signedData));

        $body = $nodeField . $timeField . $addrField . $dataField . $signField;

        return $this->compileMessage($body);
    }
}