<?php


class PubKeyByAddr extends aMultyIdDbRow
{
    /** @var string  */
    protected $table = self::ADDR_PUBKEYS_TABLE;     /* overrided */

    /** @var string  */
    protected $idFormat = DbFieldClassEnum::ADDR; /* overrided */

    protected $canBeReplaced = false;     /* overrided */

    /**
     * 'propertyName' => fieldFormat
     * @var array
     */
    protected static $fieldSet = array(
        'publicKey' =>    DbFieldClassEnum::PUBKEY,
    );

    protected $publicKey = null;
    public function setPublicKey($val, $needSave = true) : self {return $this->setNewValue($this->publicKey, $val, $needSave);}
    public function getPublicKey() : ?string {return $this->publicKey;}
}