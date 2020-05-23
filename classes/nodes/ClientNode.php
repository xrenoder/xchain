<?php


class ClientNode extends aNode
{
    /** @var int  */
    protected static $id = NodeClassEnum::CLIENT_ID;  /* overrided */

    /** @var bool  */
    protected $isClient = true;  /* overrided */

}