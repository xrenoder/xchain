<?php


class Chain extends aBase
{
    /** @var int  */
    protected $id = 0;
    public function setId(int $val) : self {$this->id = $val; return $this;}
    public function getId() : int {return $this->id;}

    public static function create(aBase $parent, int $id) : self
    {
        $me = new Chain($parent);

        $me->setId($id);

        return $me;
    }
}