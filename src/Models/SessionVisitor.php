<?php

namespace Ben182\AbTesting\Models;

use Ben182\AbTesting\Contracts\VisitorInterface;

class SessionVisitor implements VisitorInterface
{
    const SESSION_KEY_EXPERIMENT = 'ab_testing_experiment';

    public function hasExperiment()
    {
        return (bool) session(self::SESSION_KEY_EXPERIMENT);
    }

    public function getExperiment()
    {
        return session(self::SESSION_KEY_EXPERIMENT);
    }

    public function setExperiment(Experiment $next)
    {
        session([self::SESSION_KEY_EXPERIMENT => $next]);
    }
}
