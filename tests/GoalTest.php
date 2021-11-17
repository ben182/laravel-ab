<?php

namespace Ben182\AbTesting\Tests;

use Ben182\AbTesting\AbTesting;
use Ben182\AbTesting\AbTestingFacade;
use Ben182\AbTesting\Events\GoalCompleted;
use Illuminate\Support\Facades\Event;

class GoalTest extends TestCase
{
    public function test_that_goal_complete_works()
    {
        $returnedGoal = AbTestingFacade::completeGoal('firstGoal');

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENT);
        $goal = $experiment->goals->where('name', 'firstGoal')->first();

        $this->assertEquals($goal, $returnedGoal);

        $this->assertEquals(1, $goal->hit);

        $this->assertEquals(collect([$goal->id]), session(AbTesting::SESSION_KEY_GOALS));

        Event::assertDispatched(GoalCompleted::class, function ($g) use ($goal) {
            return $g->goal->id === $goal->id;
        });
    }

    public function test_that_goal_can_only_be_completed_once()
    {
        $this->test_that_goal_complete_works();

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENT);
        $goal = $experiment->goals->where('name', 'firstGoal')->first();

        $this->assertEquals(1, $goal->hit);

        $returnedGoal = AbTestingFacade::completeGoal('firstGoal');

        $this->assertFalse($returnedGoal);

        $this->assertEquals(1, $goal->hit);

        $this->assertEquals(collect([$goal->id]), session(AbTesting::SESSION_KEY_GOALS));
    }

    public function test_that_invalid_goal_name_returns_false()
    {
        $this->assertFalse(AbTestingFacade::completeGoal('1234'));
    }

    public function test_that_completed_goals_works()
    {
        AbTestingFacade::completeGoal('firstGoal');

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENT);
        $goal = $experiment->goals->where('name', 'firstGoal');

        $this->assertEquals($goal->pluck('id')->toArray(), AbTestingFacade::getCompletedGoals()->pluck('id')->toArray());
    }

    public function test_that_completeGoal_works_with_crawlers()
    {
        config([
            'ab-testing.ignore_crawlers' => true,
        ]);
        $_SERVER['HTTP_USER_AGENT'] = 'Googlebot';

        $this->assertFalse(AbTestingFacade::completeGoal('firstGoal'));
    }
}
