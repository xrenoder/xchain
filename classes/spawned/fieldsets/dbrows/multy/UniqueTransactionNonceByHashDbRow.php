<?php


class UniqueTransactionNonceByHashDbRow extends aMultyIdDbRow
{
    /** @var string  */
    protected $table = DbTableEnum::UNIQUE_TRANSACTIONS;     /* overrided */

    /** @var string  */
    protected $idFormatType = FieldFormatClassEnum::MD4_RAW; /* overrided */

    /* 'property' => '[fieldType, false or object method]' or 'formatType' */
    protected static $fieldSet = array(
        'nonce' =>    FieldFormatClassEnum::ULONG,
    );

    /** @var int  */
    protected $nonce = null;
    public function setNonce(int $val) : self {return $this->setNewValue($this->nonce, $val);}
    public function getNonce() : ?int {return $this->nonce;}
}