<?php

namespace Ben182\AbTesting\Tests;

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

    public function setUp()
    {
        parent::setUp();
        // $this->withFactories(__DIR__.'/Factories');

        $this->artisan('migrate');

        session()->flush();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'wink');
        $app['config']->set('database.connections.wink', [
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
}
