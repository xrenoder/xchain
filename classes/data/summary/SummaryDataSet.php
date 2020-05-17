<?php
/**
 * Class of current states of blockchain (last prepared block, last known block, rules, etc)
 */

class SummaryDataSet extends aDbRowsSet
{
    protected static $rows = array(
        'lastKnownBlock' => 'LastKnownBlockRow',
        'lastPreparedBlock' => 'LastPreparedBlockRow',
        'transactionEmissionRule' => 'TransactionEmissionRuleRow',
        'transactionRewardsRule' => 'TransactionRewardsRuleRow',
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

    public static function create(App $app)
    {
        $me = new static($app);

        $me
            ->fillRows()
            ->getApp()->setSummaryDataSet($me);

        return $me;
    }
}