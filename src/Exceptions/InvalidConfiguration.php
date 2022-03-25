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
    
    public static function percentage(): self
    {
        return new static('There is no percentage for every experiment');
    }
    
    public static function totalPercentage(): self
    {
        return new static('Total percentage should be equal to 100');
    }
    
    public static function numericPercentages(): self
    {
        return new static('Percentages should be numeric');
    }
    
    public static function interval(): self
    {
        return new static('The elements of interval array must be dates of format Y-m-d H:i:s and the first element less than the second');
    }
}
