<?php

namespace Ben182\AbTesting\Events;

class ExperimentNewVisitor
{
    public $experiment;

    public function __construct($experiment)
    {
        $this->experiment = $experiment;
    }
}
