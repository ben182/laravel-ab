<?php

namespace Ben182\AbTesting;

use Ben182\AbTesting\Models\Goal;
use Illuminate\Support\Collection;
use Ben182\AbTesting\Models\Experiment;
use Ben182\AbTesting\Exceptions\InvalidConfiguration;

class AbTesting
{
    protected $experiments;

    const SESSION_KEY_EXPERIMENTS = 'ab_testing_experiment';
    const SESSION_KEY_GOALS = 'ab_testing_goals';

    public function __construct()
    {
        $this->experiments = new Collection;
    }

    protected function start()
    {
        $configExperiments = config('ab-testing.experiments');
        $configGoals = config('ab-testing.goals');

        if (count($configExperiments) !== count(array_unique($configExperiments))) {
            throw InvalidConfiguration::experiment();
        }

        if (count($configGoals) !== count(array_unique($configGoals))) {
            throw InvalidConfiguration::goal();
        }

        foreach ($configExperiments as $configExperiment) {
            $this->experiments[] = $experiment = Experiment::firstOrCreate([
                'name' => $configExperiment,
            ], [
                'visitors' => 0,
            ]);

            foreach ($configGoals as $configGoal) {
                $experiment->goals()->firstOrCreate([
                    'name' => $configGoal,
                ], [
                    'hit' => 0,
                ]);
            }
        }

        session([
            self::SESSION_KEY_GOALS => new Collection,
        ]);
    }

    public function pageview()
    {
        if (! session(self::SESSION_KEY_EXPERIMENTS)) {
            $this->start();
            $this->setNextExperiment();
        }
    }

    protected function setNextExperiment()
    {
        $next = $this->getNextExperiment();
        $next->incrementVisitor();

        session([
            self::SESSION_KEY_EXPERIMENTS => $next,
        ]);
    }

    protected function getNextExperiment()
    {
        $sorted = $this->experiments->sortBy('visitors');

        return $sorted->first();
    }

    public function isExperiment($name)
    {
        $this->pageview();

        return $this->getExperiment()->name === $name;
    }

    public function completeGoal($goal)
    {
        if (! $this->getExperiment()) {
            return false;
        }

        $goal = $this->getExperiment()->goals->where('name', $goal)->first();

        if (! $goal) {
            return false;
        }

        if (session(self::SESSION_KEY_GOALS)->contains($goal->id)) {
            return false;
        }

        session(self::SESSION_KEY_GOALS)->push($goal->id);

        return tap($goal)->incrementHit();
    }

    public function getExperiment()
    {
        return session(self::SESSION_KEY_EXPERIMENTS);
    }

    public function getCompletedGoals()
    {
        if (! session(self::SESSION_KEY_GOALS)) {
            return false;
        }

        return session(self::SESSION_KEY_GOALS)->map(function ($goalId) {
            return Goal::find($goalId);
        });
    }
}
