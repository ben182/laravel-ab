<?php

namespace Ben182\AbTesting\Exceptions;

use Exception;

class InvalidConfiguration extends Exception
{
    public static function noExperiment(): self
    {
        return new static('There are no experiments set.');
    }

    public static function experiment(): self
    {
        return new static('The experiment names should be unique.');
    }

    public static function goal(): self
    {
        return new static('The goal names should be unique.');
    }
}
