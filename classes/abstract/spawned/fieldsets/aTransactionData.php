<?php


abstract class aTransactionData extends aFieldSet
{
    protected static $dbgLvl = Logger::DBG_TRANS_DATA;

    /** @var string  */
    protected static $enumClass = 'TransactionDataClassEnum'; /* overrided */

    /** @var string  */
    protected $fieldClass = 'aTransactionDataField'; /* overrided */

    protected function __construct(aTransaction $parent)
    {
        parent::__construct($parent);
        $this->fields = array_replace($this->fields, static::$fieldSet);
    }

    protected static function create(aTransaction $parent) : self
    {
        $me = new static($parent);

        $me
            ->setIdFromEnum();

        return $me;
    }

    public function createRaw() : string
    {
        $this->raw = '';

        foreach($this->fields as $fieldId => $property) {
            if ($this->$property === null) {
                $this->raw = null;
                return $this->raw;
            }

            /** @var aTransactionDataField $fieldClassName */
            $fieldClassName = TransactionDataFieldClassEnum::getItem($fieldId);
            $this->raw .= $fieldClassName::pack($this, $this->$property);
        }

        return $this->raw;
    }
}