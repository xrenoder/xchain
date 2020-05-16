<?php


abstract class aSimpleAddressMessage extends aSimpleMessage
{
    /** @var int  */
    protected $maxLen = MessageFieldClassEnum::SIMPLE_ADDR_MAX_LEN;   /* overrided */

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fields = array(
        MessageFieldClassEnum::TYPE =>      '',                // must be always first field in message
        MessageFieldClassEnum::LENGTH =>    'declaredLen',     // must be always second field in message
        MessageFieldClassEnum::NODE =>      'remoteNodeId',
        MessageFieldClassEnum::TIME =>      'sendingTime',
        MessageFieldClassEnum::ADDR =>      'remoteAddr',
    );

    /** @var string  */
    protected $remoteAddrBin = null;
    public function getRemoteAddrBin() : string {return $this->remoteAddrBin;}

    /**
     * @return string
     */
    public function createMessageString() : string
    {
        $nodeField = NodeMessageField::packField($this->getSocket()->getMyNodeId());
        $timeField = TimeMessageField::packField(time());
        $addrField = AddrMessageField::packField($this->getApp()->getMyAddr()->getAddressBin());

        $body = $nodeField . $timeField . $addrField;

        $typeField = TypeMessageField::packField(static::$id);
        $messageStringLength = strlen($typeField) + LengthMessageField::getLength() + strlen($body);
        $lenField = LengthMessageField::packField($messageStringLength);

        return $typeField . $lenField . $body;
    }
}