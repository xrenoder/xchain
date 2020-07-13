<?php


class PubKeyByAddrDbRow extends aPubKeyByAddrDbRow
{
    /** @var string  */
    protected $table = DbTableEnum::PUBLIC_KEYS;     /* overrided */
}