<?php
/**
 * Class of current states of blockchain (last prepared block, last known block, rules, etc)
 */

class ChainState extends aDbRecord
{
    protected const LAST_KNOWN_BLOCK = 'lastKnownBlock';
    protected const LAST_PREPARED_BLOCK = 'lastPreparedBlock';
    protected const TRANSACTION_REWARD_RULE = 'transactionRewardRule';
    protected const TRANSACTION_EMISSION_RULE = 'transactionEmissionRule';

    protected $fields = array(
        self::LAST_KNOWN_BLOCK => 1,
        self::LAST_PREPARED_BLOCK => 0,
        self::TRANSACTION_REWARD_RULE => 'default rule',
        self::TRANSACTION_EMISSION_RULE => 'default rule',
    );

//    public function setField($id, $val) : self {$this->fields[$id] = $val; return $this;}
//    public function getField($id) : string {return $this->fields[$id];}

    public function setLastKnownBlock(int $val) : self {return $this->setAndSaveField(self::LAST_KNOWN_BLOCK, $val);}
    public function getLastKnownBlock() : string {return $this->fields[self::LAST_KNOWN_BLOCK];}

    public function setLastPreparedBlock(int $val) : self {return $this->setAndSaveField(self::LAST_PREPARED_BLOCK, $val);}
    public function getLastPreparedBlock() : string {return $this->fields[self::LAST_PREPARED_BLOCK];}

    public function setTransactionRewardRule(int $val) : self {return $this->setAndSaveField(self::TRANSACTION_REWARD_RULE, $val);}
    public function getTransactionRewardRule() : string {return $this->fields[self::TRANSACTION_REWARD_RULE];}

    public function setTransactionEmissionRule(int $val) : self {return $this->setAndSaveField(self::TRANSACTION_EMISSION_RULE, $val);}
    public function getTransactionEmissionRule() : string {return $this->fields[self::TRANSACTION_EMISSION_RULE];}

    public static function create(App $app, string $table)
    {
        $me = new static($app);

        $me
            ->setTable($table)
            ->fillFields()
            ->getApp()->setChainState($me);

        return $me;
    }
}