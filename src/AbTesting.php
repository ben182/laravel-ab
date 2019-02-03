<?php

namespace Ben182\AbTesting;

use Ben182\AbTesting\Models\Experiment;
use Illuminate\Support\Collection;


class AbTesting
{
    protected $experiments;

    const SESSION_KEY_EXPERIMENTS = 'ab_testing_experiment';
    const SESSION_KEY_GOALS = 'ab_testing_goals';

    public function __construct() {
        $this->experiments = new Collection;
    }

    protected function start() {
        $configExperiments = config('ab-testing.experiments');
        $configGoals = config('ab-testing.goals');

        // if ($this->experiments->count() === count($configExperiments)) {
        //     return;
        // }

        if (count($configExperiments) !== count(array_unique($configExperiments))) {
            throw new \Exception('The experiment names should be unique');
        }

        if (count($configGoals) !== count(array_unique($configGoals))) {
            throw new \Exception('The goal names should be unique');
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
    }

    public function pageview() {

        if (! session(self::SESSION_KEY_EXPERIMENTS)) {
            $this->start();
            $this->setNextExperiment();
        }
    }

    protected function setNextExperiment() {
        $next = $this->getNextExperiment();
        $next->incrementVisitor();

        session([
            self::SESSION_KEY_EXPERIMENTS => $next,
        ]);
    }

    protected function getNextExperiment() {
        $sorted = $this->experiments->sortBy('visitors');
        return $sorted->first();
    }

    public function isExperiment($name) {
        $this->pageview();

        return session(self::SESSION_KEY_EXPERIMENTS)->name === $name;
    }

    public function completeGoal($goal) {

        $goal = session(self::SESSION_KEY_EXPERIMENTS)->goals->where('name', $goal)->first();

        if (!$goal) {
            return false;
        }

        if (in_array($goal->id, array_wrap(session(self::SESSION_KEY_GOALS)))) {
            return false;
        }

        $newGoals = session(self::SESSION_KEY_GOALS);
        $newGoals[] = $goal->id;

        session([
            self::SESSION_KEY_GOALS => $newGoals,
        ]);

        return $goal->incrementHit();
    }

    public function getExperiment() {
        return session(self::SESSION_KEY_EXPERIMENTS)->name;
    }
}
