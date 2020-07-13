<?php


abstract class aMultyIdDbRow extends aDbRow
{
    protected static $dbgLvl = Logger::DBG_DB_MROW;

    protected $canBeReplaced = false;     /* overrided */

    public function getId() {return $this->getType();}

    public static function create(aBase $parent, $id) : self
    {
        $me = new static($parent);
        $me
            ->setType($id)
            ->load();

        return $me;
    }


}