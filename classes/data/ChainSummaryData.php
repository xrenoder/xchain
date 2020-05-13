<?php
/**
 * Class of current states of blockchain (last prepared block, last known block, rules, etc)
 */

class ChainSummaryData extends aDbRowsCollection
{
    protected $rows = array(
        'lastKnownBlock' => 'LastKnownBlockRow',
        'lastPreparedBlock' => 'LastPreparedBlockRow',
        'transactionEmissionRule' => 'TransactionEmissionRuleRow',
        'transactionRewardsRule' => 'TransactionRewardsRuleRow',
    );

    protected $lastKnownBlock;
    public function getLastKnownBlock() : string {return $this->lastKnownBlock;}

    protected $lastPreparedBlock;
    public function getLastPreparedBlock() : string {return $this->lastPreparedBlock;}

    protected $transactionEmissionRule;
    public function getTransactionEmissionRule() : string {return $this->transactionEmissionRule;}

    protected $transactionRewardsRule;
    public function getTransactionRewardsRule() : string {return $this->transactionRewardsRule;}

    public static function create(App $app)
    {
        $me = new static($app);

        $me
            ->fillRows()
            ->getApp()->setChainSummaryData($me);

        return $me;
    }
}