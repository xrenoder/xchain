<?php
/**
 * Base classenum for blockchain transactions
 *
 * "Address-to-Chain without amount without data" (type C)
 * unfreeze all
 * undelegate all from all nodes
 */

abstract class aTransaction extends aFieldSet
{
    use tTransactionConstructor;

    protected static $dbgLvl = Logger::DBG_TRANSACT;

    /** @var string  */
    protected static $enumClass = 'TransactionClassEnum'; /* overrided */

    /** @var string  */
    protected $fieldClass = 'aTransactionField'; /* overrided */

    /** @var int  */
    protected $fieldPointer = TransactionFieldClassEnum::AUTHOR;  /* overrided */    // first fieldId prepared inside ttransaction-object (field 'Author')

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(      /* overrided */
        TransactionFieldClassEnum::TYPE =>      'id',              // must be always first field in message
    );

    protected static $fieldLastSet = array(  /* overrided */
        TransactionFieldClassEnum::SIGN =>      'signature',
        TransactionFieldClassEnum::HASH =>      'hash',
    );

    /** @var string  */
    protected $signature = null;
    public function getSignature() : string {return $this->signature;}

    /** @var string  */
    protected $hash = null;
    public function getHash() : string {return $this->hash;}


    /** @var string  */
    protected $data = null;
    public function setData(string $val) : self {$this->data = $val; return $this;}
    public function getData() : string {return $this->data;}

    /** @var string  */
    protected $signedData = null;
    public function getSignedData() : string {return $this->signedData;}

    /** @var bool  */
    protected $isIncoming = null;
    public function setIsIncoming(bool $val) : self {$this->isIncoming = $val; return $this;}
    public function isIncoming() : bool {return $this->isIncoming;}

    public function getMessage() : aTransactionMessage
    {
        if ($this->getParent() instanceof aTransactionMessage) {
            return $this->getParent();
        }

        throw new Exception("Bad code - message-object can be used only in incoming transaction");
    }

    public static function create(aBase $parent) : self
    {
        $me = new static($parent);

        $me
            ->setIdFromEnum()
            ->setFieldOffset(TransactionFieldClassEnum::getLength(TransactionFieldClassEnum::TYPE));

        if ($parent instanceof aTransactionMessage) {
// if parent object is "aTransactionMessage" - this is incoming message,
            $me->setIsIncoming(true);
            $parent->setTransaction($me);
            $me->dbg($me->getName() .  ' detected');
        } else {
            $me->setIsIncoming(false);
            $me->dbg($me->getName() .  " created");
        }

        return $me;
    }
}