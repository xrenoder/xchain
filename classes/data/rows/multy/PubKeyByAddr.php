<?php


class PubKeyByAddr extends aMultyIdDbRow
{
    /** @var string  */
    protected $table = DbTableEnum::ADDR_PUBKEYS;     /* overrided */

    /** @var string  */
    protected $idFormatType = FieldFormatClassEnum::ADDR; /* overrided */

    /* 'property' => '[fieldType, false or object method]' or 'formatType' */
    protected static $fieldSet = array(
        'addressWithPubKey' =>    [DbFieldClassEnum::PUBKEY, 'getPublicKeyBin'],
    );

    /** @var Address  */
    protected $addressWithPubKey = null;
    public function setAddressWithPubKey(Address $val, $needSave = true) : self {return $this->setNewValue($this->addressWithPubKey, $val, $needSave);}
    public function getAddressWithPubKey() : ?Address {return $this->addressWithPubKey;}
}