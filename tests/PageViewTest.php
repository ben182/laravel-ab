<?php

namespace Ben182\AbTesting\Tests;

use Ben182\AbTesting\AbTesting;
use Ben182\AbTesting\AbTestingFacade;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Compilers\BladeCompiler;

class PageViewTest extends TestCase
{
    public function test_that_pageview_works()
    {
        AbTestingFacade::pageview();

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENTS);

        $this->assertEquals($this->experiments[0], $experiment->name);
        $this->assertEquals(1, $experiment->visitors);
    }

    public function test_that_pageview_changes_after_first_test()
    {
        $this->test_that_pageview_works();

        session()->flush();

        $this->assertNull(session(AbTesting::SESSION_KEY_EXPERIMENTS));

        AbTestingFacade::pageview();

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENTS);

        $this->assertEquals($this->experiments[1], $experiment->name);
        $this->assertEquals(1, $experiment->visitors);
    }

    public function test_is_experiment()
    {
        AbTestingFacade::pageview();

        $this->assertTrue(AbTestingFacade::isExperiment('firstExperiment'));
        $this->assertFalse(AbTestingFacade::isExperiment('secondExperiment'));

        $this->assertEquals('firstExperiment', AbTestingFacade::getExperiment()->name);
    }

    public function test_that_two_pageviews_do_not_count_as_two_visitors()
    {
        AbTestingFacade::pageview();
        AbTestingFacade::pageview();

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENTS);

        $this->assertEquals(1, $experiment->visitors);
    }

    public function test_that_isExperiment_triggers_pageview()
    {
        AbTestingFacade::isExperiment('firstExperiment');

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENTS);

        $this->assertEquals($this->experiments[0], $experiment->name);
        $this->assertEquals(1, $experiment->visitors);
    }

    public function test_request_macro() {
        $this->newVisitor();

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENTS);

        $this->assertEquals($experiment, request()->abExperiment());
    }
}
