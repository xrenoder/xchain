<?php


class FixedIdDbRowClassEnum extends aClassEnum
{
    protected static $baseClassName = 'aFixedIdDbRow'; /* overrided */

    public const LAST_KNOWN_BLOCK = 'lastKnownBlock';
    public const LAST_PREPARED_BLOCK = 'lastPreparedBlock';
    public const TRANSACTION_EMISSION_RULE = 'transactionEmissionRule';
    public const TRANSACTION_REWARDS_RULE = 'transactionRewardsRule';

    protected static $items = array(
        self::LAST_KNOWN_BLOCK => 'LastKnownBlockRow',
        self::LAST_PREPARED_BLOCK => 'LastPreparedBlockRow',
        self::TRANSACTION_EMISSION_RULE => 'TransactionEmissionRuleRow',
        self::TRANSACTION_REWARDS_RULE => 'TransactionRewardsRuleRow',
    );

/*
    protected static $data = array(
        self::LAST_KNOWN_BLOCK => DbTableEnum::SUMMARY,
        self::LAST_PREPARED_BLOCK => DbTableEnum::SUMMARY,
        self::TRANSACTION_EMISSION_RULE => DbTableEnum::SUMMARY,
        self::TRANSACTION_REWARDS_RULE => DbTableEnum::SUMMARY,
    );

    public static function getTable(string $id) : ?string
    {
        return static::$data[$id] ?? null;
    }
*/
}