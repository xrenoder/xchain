<?php


abstract class aTransactionCaT extends aTransactionCa
{
    use tTransactionConstructor;

    /**
     * fieldId => 'propertyName'
     * @var string[]
     */
    protected static $fieldSet = array(      /* overrided */
        TransactionFieldClassEnum::TINY_DATA =>    'data',
    );


}