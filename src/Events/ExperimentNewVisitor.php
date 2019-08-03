<?php

namespace Ben182\AbTesting\Events;

use Ben182\AbTesting\Contracts\VisitorInterface;

class ExperimentNewVisitor
{
    public $experiment;

    public function __construct($experiment, VisitorInterface $visitor)
    {
        $this->experiment = $experiment;
        $this->visitor = $visitor;
    }
}
