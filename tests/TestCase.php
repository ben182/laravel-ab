<?php

namespace Ben182\AbTesting\Tests;

use Ben182\AbTesting\AbTestingFacade;
use Illuminate\Support\Facades\Event;
use Ben182\AbTesting\AbTestingServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $experiments = [
        'firstExperiment',
        'secondExperiment',
    ];
    protected $goals = [
        'firstGoal',
        'secondGoal',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');

        session()->flush();

        Event::fake();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'ab');
        $app['config']->set('database.connections.ab', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('ab-testing.experiments', $this->experiments);
        $app['config']->set('ab-testing.goals', $this->goals);
    }

    protected function getPackageProviders($app)
    {
        return [AbTestingServiceProvider::class];
    }

    protected function newVisitor()
    {
        session()->flush();
        AbTestingFacade::pageView();
    }

    protected function actingAsCrawler()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'crawl';
        config()->set('ab-testing.ignore_crawlers', true);
    }
}
