<?php


abstract class aMessageData extends aFieldSet
{
    protected static $dbgLvl = Logger::DBG_MESSAGE_DATA;

    /** @var string  */
    protected static $enumClass = 'MessageDataClassEnum'; /* overrided */

    /** @var string  */
    protected $fieldClass = 'aMessageDataField'; /* overrided */

    public function getMessage() : aDataMessage {return $this->getParent();}

    protected function __construct(aDataMessage $parent)
    {
        parent::__construct($parent);
        $this->fields = array_replace($this->fields, static::$fieldSet);
    }

    protected static function create(aDataMessage $parent) : self
    {
        $me = new static($parent);

        $me
            ->setTypeFromEnum();

        return $me;
    }
}