<?php

namespace Ben182\AbTesting\Exceptions;

use Exception;

class InvalidConfiguration extends Exception
{
    public static function experiment(): self
    {
        return new static('The experiment names should be unique.');
    }

    public static function goal(): self
    {
        return new static('The goal names should be unique.');
    }
}
