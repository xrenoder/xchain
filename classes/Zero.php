<?php


class Zero extends aBase
{
    public static function create(App $app) : self
    {
        return new static($app);
    }

    public function run()
    {

    }
}