<?php

namespace Ben182\AbTesting;

use Ben182\AbTesting\Contracts\VisitorInterface;
use Ben182\AbTesting\Events\ExperimentNewVisitor;
use Ben182\AbTesting\Events\GoalCompleted;
use Ben182\AbTesting\Exceptions\InvalidConfiguration;
use Ben182\AbTesting\Models\DatabaseVisitor;
use Ben182\AbTesting\Models\Experiment;
use Ben182\AbTesting\Models\Goal;
use Ben182\AbTesting\Models\SessionVisitor;
use Illuminate\Support\Collection;

class AbTesting
{
    protected $experiments;
    protected $visitor;

    public const SESSION_KEY_GOALS = 'ab_testing_goals';

    public function __construct()
    {
        $this->experiments = new Collection();
    }

    /**
     * Validates the config items and puts them into models.
     */
    protected function start()
    {
        $configExperiments = config('ab-testing.experiments');
        $configGoals = config('ab-testing.goals');

        if (!count($configExperiments)) {
            throw InvalidConfiguration::noExperiment();
        }

        if (count($configExperiments) !== count(array_unique($configExperiments))) {
            throw InvalidConfiguration::experiment();
        }

        if (count($configGoals) !== count(array_unique($configGoals))) {
            throw InvalidConfiguration::goal();
        }

        foreach ($configExperiments as $configExperiment) {
            $this->experiments[] = $experiment = Experiment::with('goals')->firstOrCreate([
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
            self::SESSION_KEY_GOALS => new Collection(),
        ]);
    }

    /**
     * Resets the visitor data.
     */
    public function resetVisitor()
    {
        session()->flush();
        $this->visitor = null;
    }

    /**
     * Triggers a new visitor. Picks a new experiment and saves it to the Visitor.
     *
     * @param int $visitor_id An optional visitor identifier
     *
     * @return \Ben182\AbTesting\Models\Experiment|void
     */
    public function pageView($visitor_id = null)
    {
        $visitor = $this->getVisitor($visitor_id);

        if (!session(self::SESSION_KEY_GOALS) || $this->experiments->isEmpty()) {
            $this->start();
        }

        if ($visitor->hasExperiment()) {
            return $visitor->getExperiment();
        }

        $this->setNextExperiment($visitor);

        event(new ExperimentNewVisitor($this->getExperiment(), $visitor));

        return $this->getExperiment();
    }

    /**
     * Calculates a new experiment and sets it to the Visitor.
     *
     * @param VisitorInterface $visitor An object implementing VisitorInterface
     */
    protected function setNextExperiment(VisitorInterface $visitor)
    {
        $next = $this->getNextExperiment();
        $next->incrementVisitor();

        $visitor->setExperiment($next);
    }

    /**
     * Calculates a new experiment.
     *
     * @return \Ben182\AbTesting\Models\Experiment
     */
    protected function getNextExperiment()
    {
        $sorted = $this->experiments->sortBy('visitors');

        return $sorted->first();
    }

    /**
     * Checks if the currently active experiment is the given one.
     *
     * @param string $name The experiments name
     *
     * @return bool
     */
    public function isExperiment(string $name)
    {
        $this->pageView();

        if (!$experiment = $this->getExperiment()) {
            return false;
        }

        return $experiment->name === $name;
    }

    /**
     * Completes a goal by incrementing the hit property of the model and setting its ID in the session.
     *
     * @param string $goal       The goals name
     * @param int    $visitor_id An optional visitor identifier
     *
     * @return \Ben182\AbTesting\Models\Goal|false
     */
    public function completeGoal(string $goal, $visitor_id = null)
    {
        $this->pageView($visitor_id);

        $goal = $this->getExperiment($visitor_id)->goals->where('name', $goal)->first();

        if (!$goal) {
            return false;
        }

        if (session(self::SESSION_KEY_GOALS)->contains($goal->id)) {
            return false;
        }

        session(self::SESSION_KEY_GOALS)->push($goal->id);

        $goal->incrementHit();
        event(new GoalCompleted($goal));

        return $goal;
    }

    /**
     * Returns the currently active experiment.
     *
     * @param int $visitor_id An optional visitor identifier
     *
     * @return null|\Ben182\AbTesting\Models\Experiment
     */
    public function getExperiment($visitor_id = null)
    {
        return $this->getVisitor($visitor_id)->getExperiment();
    }

    /**
     * Returns all the completed goals.
     *
     * @return false|\Illuminate\Support\Collection
     */
    public function getCompletedGoals()
    {
        if (!session(self::SESSION_KEY_GOALS)) {
            return false;
        }

        return session(self::SESSION_KEY_GOALS)->map(function ($goalId) {
            return Goal::find($goalId);
        });
    }

    /**
     * Returns a visitor instance.
     *
     * @param int $visitor_id An optional visitor identifier
     *
     * @return \Ben182\AbTesting\Models\DatabaseVisitor|\Ben182\AbTesting\Models\SessionVisitor
     */
    public function getVisitor($visitor_id = null)
    {
        if (!is_null($this->visitor)) {
            return $this->visitor;
        }

        if (!empty($visitor_id)) {
            return $this->visitor = DatabaseVisitor::firstOrNew(['visitor_id' => $visitor_id]);
        }

        return $this->visitor = new SessionVisitor();
    }
}
