<?php

namespace Ben182\AbTesting\Tests;

use Ben182\AbTesting\AbTesting;
use Ben182\AbTesting\AbTestingFacade;
use Ben182\AbTesting\Events\ExperimentNewVisitor;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;

class PageViewTest extends TestCase
{
    public function test_that_pageview_works()
    {
        AbTestingFacade::pageView();

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENT);

        $this->assertEquals($this->experiments[0], $experiment->name);
        $this->assertEquals(1, $experiment->visitors);

        Event::assertDispatched(ExperimentNewVisitor::class, function ($e) use ($experiment) {
            return $e->experiment->id === $experiment->id;
        });
    }

    public function test_that_pageview_changes_after_first_test()
    {
        $this->test_that_pageview_works();

        session()->flush();

        $this->assertNull(session(AbTesting::SESSION_KEY_EXPERIMENT));

        AbTestingFacade::pageView();

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENT);

        $this->assertEquals($this->experiments[1], $experiment->name);
        $this->assertEquals(1, $experiment->visitors);
    }

    public function test_that_pageview_does_not_trigger_for_crawlers()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'crawl';
        config()->set('ab-testing.ignore_crawlers', true);

        AbTestingFacade::pageView();

        $this->assertNull(session(AbTesting::SESSION_KEY_EXPERIMENT));

        Event::assertNotDispatched(ExperimentNewVisitor::class);
    }

    public function test_is_experiment()
    {
        AbTestingFacade::pageView();

        $this->assertTrue(AbTestingFacade::isExperiment('firstExperiment'));
        $this->assertFalse(AbTestingFacade::isExperiment('secondExperiment'));

        $this->assertEquals('firstExperiment', AbTestingFacade::getExperiment()->name);
    }

    public function test_that_two_pageviews_do_not_count_as_two_visitors()
    {
        AbTestingFacade::pageView();
        AbTestingFacade::pageView();

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENT);

        $this->assertEquals(1, $experiment->visitors);
    }

    public function test_that_isExperiment_triggers_pageview()
    {
        AbTestingFacade::isExperiment('firstExperiment');

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENT);

        $this->assertEquals($this->experiments[0], $experiment->name);
        $this->assertEquals(1, $experiment->visitors);
    }

    public function test_request_macro()
    {
        $this->newVisitor();

        $experiment = session(AbTesting::SESSION_KEY_EXPERIMENT);

        $this->assertEquals($experiment, request()->abExperiment());
    }

    public function test_blade_macro()
    {
        $this->newVisitor();

        $this->assertTrue(Blade::check('ab', 'firstExperiment'));
    }

    public function test_that_isExperiment_works_with_crawlers()
    {
        config([
            'ab-testing.ignore_crawlers' => true,
        ]);
        $_SERVER['HTTP_USER_AGENT'] = 'Googlebot';

        $this->assertFalse(AbTestingFacade::isExperiment('firstExperiment'));
    }
}
