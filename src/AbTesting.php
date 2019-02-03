<?php

namespace Ben182\AbTesting;

use Ben182\AbTesting\Models\Experiment;
use Illuminate\Support\Collection;


class AbTesting
{
    protected $experiments;

    public function __construct() {
        // dump('construct');
        $this->experiments = new Collection;
    }

    protected function start() {
        $configExperiments = config('ab-testing.experiments');
        $configGoals = config('ab-testing.goals');

        if ($this->experiments->count() === count($configExperiments)) {
            return;
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

        if (! session('ab_testing_experiment')) {
            $this->start();
            $this->setNewExperiment();
        }
    }

    protected function setNewExperiment() {
        $next = $this->nextExperiment();
        $next->incrementVisitor();

        session([
            'ab_testing_experiment' => $next,
        ]);
    }

    protected function nextExperiment() {
        $sorted = $this->experiments->sortBy('visitors');
        return $sorted->first();
    }

    public function isExperiment($name) {
        $this->pageview();

        return session('ab_testing_experiment')->name === $name;
    }

    public function completeGoal($goal) {

        $goal = session('ab_testing_experiment')->goals->where('name', $goal)->first();

        if (!$goal) {
            return false;
        }

        // dump(in_array($goal->id, session('ab_testing_goals')));

        if (in_array($goal->id, array_wrap(session('ab_testing_goals')))) {
            return false;
        }

        $newGoals = session('ab_testing_goals');
        $newGoals[] = $goal->id;

        session([
            'ab_testing_goals' => $newGoals,
        ]);

        return $goal->incrementHit();
    }

    public function getExperiment() {
        return session('ab_testing_experiment')->name;
    }
}
