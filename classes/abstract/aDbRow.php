<?php
/**
 * Base for storable objects classes (transactions, blocks, settings etc)
 */
abstract class aDbRow extends aBase implements constDbTables, constDbRowIds
{
    protected static $dbgLvl = Logger::DBG_ROW;

    /** @var string  */
    protected static $table = null;     /* override me */

    protected $id = null; /* can be overrided */
    protected $idFormat = null; /* override me */

    private $internalId = null;


    protected static $canBeReplaced = null;     /* override me */

    protected $data = null;
    public function getData() : ?string {return $this->data;}

    /**
     * 'propertyName' => fieldFormat
     * @var array
     */
    protected static $fields = array(
        'value' =>    FieldFormatEnum::NOPACK,
    );

    protected $isChanged = false;

    protected $value = null;   /* override me with default value or null */
    public function getValue() {return $this->value;}

    public function setValue($val, $needSave = true) : self
    {
        if ($val !== $this->value) {
            $this->value = $val;
            $this->saveIfNeed($needSave);
        }

        return $this;
    }

    public static function create(App $app, $id = null)
    {
        $me = new static($app);

        if ($id !== null) {
            $me->setId($id);
        }

        $me->load();

        return $me;
    }

    public function setId($val) : self
    {
        if ($this->id === null) {
            $this->id = $val;
            $this->packId();
        } else {
            throw new Exception( get_class($this) .  ": cannot change id $this->id to $val");
        }

        return $this;
    }

    private function packId() {
        $this->internalId = FieldFormatEnum::pack($this->id, $this->idFormat);
    }

    public function check() : bool
    {
        if (!$this->internalId === null) $this->packId();
        return $this->getApp()->getDba()->check(static::$table, $this->internalId);
    }

    public function load() : self
    {
        if (!$this->internalId === null) $this->packId();
        $this->data = $this->getApp()->getDba()->fetch(static::$table, $this->internalId);
        $this->unpackFields();

        if ($this->data !== null) {
            $this->dbg(get_class($this) . " loaded");
        } else {
            $this->dbg(get_class($this) . " loaded: NULL");
        }

        return $this;
    }

    public function save(bool $replace = false) : self
    {
        if (!$this->isChanged) {
            return $this;
        }

        $this->packFields();

        if ($this->data === null) {
            return $this;
        }

        if (!$this->internalId === null) $this->packId();

        if ($replace && $this->check()) {
            $this->getApp()->getDba()->update(static::$table, $this->internalId, $this->data);
        } else {
            $this->getApp()->getDba()->insert(static::$table, $this->internalId, $this->data);
        }

        $this->dbg(get_class($this) . " saved");

        $this->isChanged = false;

        return $this;
    }

    protected function saveIfNeed(bool $needSave) : self
    {
        $this->isChanged = true;

        if ($needSave) {
            $this->save(static::$canBeReplaced);
        }

        return $this;
    }

    private function unpackFields() : self
    {
        if ($this->data === null) {
            return $this;
        }

        $offset = 0;

        foreach(static::$fields as $property => $formatId) {
            [$length, $this->$property] = FieldFormatEnum::unpack($this->data, $formatId, $offset);

            if ($length === null || $this->$property === null) {
                break;
            }

            $offset += $length;
        }

        return $this;
    }

    private function packFields() : self
    {
        $this->data = '';

        foreach(static::$fields as $property => $formatId) {
            if ($this->$property === null) {
                $this->data = null;
                return $this;
            }

            $this->data .= FieldFormatEnum::pack($this->$property, $formatId);
        }

        return $this;
    }
}