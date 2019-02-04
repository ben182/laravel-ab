<?php

namespace Ben182\AbTesting\Tests;

use Ben182\AbTesting\Models\Experiment;
use Ben182\AbTesting\AbTesting;

class PageViewTest extends TestCase
{
    public function test_that_pageview_works() {
        app(AbTesting::class)->pageview();

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENTS);

        $this->assertEquals($this->experiments[0], $experiment->name);
        $this->assertEquals(1, $experiment->visitors);
    }

    public function test_that_pageview_changes_after_first_test() {
        $this->test_that_pageview_works();

        session()->flush();

        $this->assertNull(session(AbTesting::SESSION_KEY_EXPERIMENTS));


        app(AbTesting::class)->pageview();

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENTS);

        $this->assertEquals($this->experiments[1], $experiment->name);
        $this->assertEquals(1, $experiment->visitors);
    }

    public function test_is_experiment() {
        app(AbTesting::class)->pageview();

        $this->assertTrue(app(AbTesting::class)->isExperiment('firstExperiment'));
        $this->assertFalse(app(AbTesting::class)->isExperiment('secondExperiment'));

        $this->assertEquals('firstExperiment', app(AbTesting::class)->getExperiment()->name);
    }

    public function test_that_two_pageviews_do_not_count_as_two_visitors() {
        app(AbTesting::class)->pageview();
        app(AbTesting::class)->pageview();

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENTS);

        $this->assertEquals(1, $experiment->visitors);
    }

    public function test_that_isExperiment_triggers_pageview() {
        app(AbTesting::class)->isExperiment('firstExperiment');

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENTS);

        $this->assertEquals($this->experiments[0], $experiment->name);
        $this->assertEquals(1, $experiment->visitors);
    }
}
