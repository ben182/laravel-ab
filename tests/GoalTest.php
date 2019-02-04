<?php

namespace Ben182\AbTesting\Tests;

use Ben182\AbTesting\AbTesting;
use Ben182\AbTesting\Models\Goal;

class GoalTest extends TestCase
{
    public function test_that_goal_complete_works() {
        app(AbTesting::class)->pageview();

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENTS);
        $goal = $experiment->goals->where('name', 'firstGoal')->first();

        $this->assertEquals(0, $goal->hit);


        app(AbTesting::class)->completeGoal('firstGoal');

        $this->assertEquals(1, $goal->hit);

        $this->assertEquals(collect([$goal->id]), session(AbTesting::SESSION_KEY_GOALS));
    }

    public function test_that_goal_can_only_be_completed_once() {
        $this->test_that_goal_complete_works();

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENTS);
        $goal = $experiment->goals->where('name', 'firstGoal')->first();

        $this->assertEquals(1, $goal->hit);

        app(AbTesting::class)->completeGoal('firstGoal');

        $this->assertEquals(1, $goal->hit);

        $this->assertEquals(collect([$goal->id]), session(AbTesting::SESSION_KEY_GOALS));
    }

    public function test_that_invalid_goal_name_returns_false() {
        $this->assertFalse(app(AbTesting::class)->completeGoal('1234'));
    }

    public function test_that_completed_goals_works() {
        app(AbTesting::class)->pageview();
        app(AbTesting::class)->completeGoal('firstGoal');

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENTS);
        $goal = $experiment->goals->where('name', 'firstGoal')->first();

        $this->assertEquals(collect([$goal]), app(AbTesting::class)->getCompletedGoals());
    }
}
