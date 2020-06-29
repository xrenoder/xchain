<?php


class LastKnownBlockRow extends aSimpleFixedIdDbRow
{
    /* 'property' => '[fieldType, false or object method]' or 'formatType' */
    protected static $fieldSet = array(
        'value' => FieldFormatClassEnum::UBIG,
    );
}