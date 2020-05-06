<?php
/**
 * Class of current states of blockchain (last prepared block, last known block, rules, etc)
 */

class ChainState extends aDbRecord
{
    protected static $dbaTable = STATES_TABLE; /* overrided */

    private const LAST_KNOWN_BLOCK = 'lastKnownBlock';
    private const LAST_PREPARED_BLOCK = 'lastPreparedBlock';
    private const TRANSACTION_REWARD_RULE = 'transactionRewardRule';
    private const TRANSACTION_EMISSION_RULE = 'transactionEmissionRule';

    private $fields = array(
        self::LAST_KNOWN_BLOCK => 1,
        self::LAST_PREPARED_BLOCK => 0,
        self::TRANSACTION_REWARD_RULE => 'default rule',
        self::TRANSACTION_EMISSION_RULE => 'default rule',
    );

    public function setField($id, $val) : self {$this->fields[$id] = $val; return $this;}
    public function getField($id) : string {return $this->fields[$id];}

    public static function create(App $app)
    {
        $me = new static($app);

        $me
            ->fill()
            ->getApp()->setChainState($me);

        return $me;
    }

    public function fill() {
        $this->dbTrans();

        $rowCnt =0;

        if ($this->id = $this->first()) {
            do {
                $this->fields[$this->id] = $this->load();
            } while ($this->id = $this->next());
        }

        if ($rowCnt !== count($this->fields)) {
            foreach($this->fields as $this->id => $this->data) {
                $this->save(true);
            }
        }

        $this->dbCommit();

        return $this;
    }
}