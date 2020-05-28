<?php


class SignFormat extends aAsIsVarLengthFieldFormat
{
    /** @var string  */
    protected $id = FieldFormatClassEnum::SIGN_LC;  /* overrided */

    /** @var string  */
    protected $lengthFormatId = FieldFormatClassEnum::UCHAR;  /* overrided */

}