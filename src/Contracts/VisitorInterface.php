<?php

namespace Ben182\AbTesting\Contracts;

use Ben182\AbTesting\Models\Experiment;

interface VisitorInterface
{
    public function hasExperiment();

    public function getExperiment();

    public function setExperiment(Experiment $experiment);
}
