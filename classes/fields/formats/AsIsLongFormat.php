<?php


class AsIsLongFormat extends aAsIsVarLengthFieldFormat
{
    /** @var string  */
    protected $id = FieldFormatClassEnum::ASIS_LBE;  /* overrided */

    /** @var string  */
    protected $lengthFormatId = FieldFormatClassEnum::ULONG_BE;  /* overrided */
}