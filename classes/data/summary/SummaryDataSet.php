<?php
/**
 * Class of current states of blockchain (last prepared block, last known block, rules, etc)
 */

class SummaryDataSet extends aDbRowsSet
{
    /**
     * 'propertyName' => 'rowClassName'
     * @var string[]
     */
    protected static $rows = array(
        self::LAST_KNOWN_BLOCK => 'LastKnownBlockRow',
        self::LAST_PREPARED_BLOCK => 'LastPreparedBlockRow',
        self::TRANSACTION_EMISSION_RULE => 'TransactionEmissionRuleRow',
        self::TRANSACTION_REWARDS_RULE => 'TransactionRewardsRuleRow',
    );

    /** @var LastKnownBlockRow */
    protected $lastKnownBlock;
    public function getLastKnownBlock() : ?LastKnownBlockRow {return $this->lastKnownBlock;}

    /** @var LastPreparedBlockRow */
    protected $lastPreparedBlock;
    public function getLastPreparedBlock() : ?LastPreparedBlockRow {return $this->lastPreparedBlock;}

    /** @var TransactionEmissionRuleRow */
    protected $transactionEmissionRule;
    public function getTransactionEmissionRule() : ?TransactionEmissionRuleRow {return $this->transactionEmissionRule;}

    /** @var TransactionRewardsRuleRow */
    protected $transactionRewardsRule;
    public function getTransactionRewardsRule() : ?TransactionRewardsRuleRow {return $this->transactionRewardsRule;}

    public static function create(aLocator $locator)
    {
        $me = new static($locator);

        $me
            ->fillRows()
            ->getLocator()->setSummaryDataSet($me);

// TODO точно ли ее нужно вызывать в воркерах - как будут синхронизироваться между собой
        return $me;
    }
}