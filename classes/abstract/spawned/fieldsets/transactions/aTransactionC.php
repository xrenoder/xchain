<?php
/**
 * "Address-to-Chain without amount without data" (type C)
 * unfreeze all
 * undelegate all from all nodes
 *
 * 115 bytes
 */


abstract class aTransactionC extends aTransaction
{
    use tTransactionConstructor;

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(      /* overrided */
        TransactionFieldClassEnum::AUTHOR =>    'authorAddrBin',
    );

    /** @var string  */
    protected $authorAddrBin = null;
    public function getAuthorAddrBin() : string {return $this->authorAddrBin;}

    /** @var Address  */
    protected $authorAddress = null;
    public function setAuthorAddress(Address $val) : self {$this->authorAddress = $val; $this->authorAddrBin = $val->getAddressBin(); return $this;}
    public function getAuthorAddress() : Address {return $this->authorAddress;}



}