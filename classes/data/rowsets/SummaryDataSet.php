<?php
/**
 * Class of current states of blockchain (last prepared block, last known block, rules, etc)
 */

class SummaryDataSet extends aDbRowsSet
{
    /**
     * 'rowId' => 'propertyName'
     * @var string[]
     */
    protected static $rows = array(
        FixedIdDbRowClassEnum::LAST_KNOWN_BLOCK => 'lastKnownBlock',
        FixedIdDbRowClassEnum::LAST_PREPARED_BLOCK => 'lastPreparedBlock',
        FixedIdDbRowClassEnum::TRANSACTION_EMISSION_RULE => 'transactionEmissionRule',
        FixedIdDbRowClassEnum::TRANSACTION_REWARDS_RULE => 'transactionRewardsRule',
    );

    /** @var LastKnownBlockRow */
    protected $lastKnownBlock;
    public function getLastKnownBlock() : ?LastKnownBlockRow {return $this->lastKnownBlock;}

    /** @var LastPreparedBlockRow */
    protected $lastPreparedBlock;
    public function getLastPreparedBlock() : ?LastPreparedBlockRow {return $this->lastPreparedBlock;}

    /** @var TransactionEmissionRuleRowFixedId */
    protected $transactionEmissionRule;
    public function getTransactionEmissionRule() : ?TransactionEmissionRuleRowFixedId {return $this->transactionEmissionRule;}

    /** @var TransactionRewardsRuleRowFixedId */
    protected $transactionRewardsRule;
    public function getTransactionRewardsRule() : ?TransactionRewardsRuleRowFixedId {return $this->transactionRewardsRule;}

    public static function create(aLocator $locator)
    {
        $me = new static($locator);

        $me
            ->fillRows()
            ->getLocator()->setSummaryDataSet($me);

// TODO точно ли ее нужно вызывать в воркерах - как будут синхронизироваться между собой
// либо сделать отдельные воркеры для обработки блоков и записи в БД
        return $me;
    }
}