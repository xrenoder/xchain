<?php


class FixedIdDbRowClassEnum extends aClassEnum
{
    protected static $baseClassName = 'aFixedIdDbRow'; /* overrided */

    public const TRANSACTION_EMISSION_RULE = 'transactionEmissionRule';
    public const TRANSACTION_REWARDS_RULE = 'transactionRewardsRule';

    protected static $items = array(
        self::TRANSACTION_EMISSION_RULE => 'TransactionEmissionRuleDbRow',
        self::TRANSACTION_REWARDS_RULE => 'TransactionRewardsRuleDbRow',
    );

/*
    protected static $data = array(
        self::TRANSACTION_EMISSION_RULE => DbTableEnum::SUMMARY,
        self::TRANSACTION_REWARDS_RULE => DbTableEnum::SUMMARY,
    );

    public static function getTable(string $id) : ?string
    {
        return static::$data[$id] ?? null;
    }
*/
}