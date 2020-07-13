<?php


class NewSignerTransactionByHashDbRow extends aNewTransactionByHashDbRow
{
    /** @var string  */
    protected $table = DbTableEnum::NEW_SIGNER_TRANSACTIONS;     /* overrided */
}