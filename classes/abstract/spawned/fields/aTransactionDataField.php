<?php


abstract class aTransactionDataField extends aField
{
    protected static $dbgLvl = Logger::DBG_TRANS_DATA_FLD;

    /** @var string  */
    protected static $parentClass = 'aTransactionData'; /* overrided */

    /** @var string  */
    protected static $enumClass = 'TransactionDataFieldClassEnum'; /* overrided */
}