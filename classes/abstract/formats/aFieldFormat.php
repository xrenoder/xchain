<?php


abstract class aFieldFormat extends aBase
{
    protected static $dbgLvl = Logger::DBG_FLD_FMT;

    /** @var string  */
    protected $id;  /* override me */

    public function getId() : string {return $this->id;}

    /** @var string */
    protected $name;
    public function setName() : self {$this->name = FieldFormatClassEnum::getItem($this->id); return $this;}
    public function getName() : string {return $this->name;}

    /** @var int  */
    protected $length = null;
    public function setLength(int $val) : self {$this->length = $val; return $this;}
    public function getLength() : int {return $this->length;}

    /** @var bool */
    protected $isLast;
    public function setIsLast() : self {$this->isLast = FieldFormatClassEnum::isLast($this->id); return $this;}
    public function isLast() : bool {return $this->isLast;}

    /** @var int  */
    protected $offset = null;
    public function setOffset(int $val) : self {$this->offset = $val; return $this;}
    public function getOffset() : int {return $this->offset;}

    protected $raw = null;
    public function getRaw() : string {return $this->raw;}

    protected $value = null;
    public function getValue() {return $this->value;}

    protected static function create(aBase $parent, int $offset = 0) : ?self
    {
        $me = new static($parent);

        $me
            ->setLength(FieldFormatClassEnum::getLength($me->getId()))
            ->setIsLast()
            ->setOffset($offset)
            ->setName();

        $me->dbg(FieldFormatClassEnum::getItem($me->getId()) .  " object created");

        return $me;
    }

    public static function spawn(aBase $parent, int $id, int $offset = 0) : self
    {
        /** @var aFieldFormat $className */

        if ($className = MessageFieldClassEnum::getClassName($id)) {
            return $className::create($parent, $offset);
        }

        throw new Exception("Bad code - unknown field format class for ID " . $id);
    }

    /* can be overrided */
    public function packField($data) : string
    {
        return $this->packDataTransform($data);
    }

    public function unpackField(string $data)
    {
        $this->raw = substr($data, $this->offset, $this->length);
        return $this->unpackRawTransform();
    }

    protected function packDataTransform($data) : string
    {
        return pack($this->id, $data);
    }

    protected function unpackRawTransform()
    {
        $this->value = unpack($this->id, $this->raw)[1];
        return $this->value;
    }
}