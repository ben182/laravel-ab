<?php

namespace Ben182\AbTesting\Tests;

use Ben182\AbTesting\AbTesting;
use Ben182\AbTesting\Models\Experiment;
use Ben182\AbTesting\Models\Goal;
use Ben182\AbTesting\Commands\FlushCommand;

class CommandTest extends TestCase
{
    public function test_flush_command() {
        $this->assertCount(0, Experiment::all());
        $this->assertCount(0, Goal::all());

        app(AbTesting::class)->pageview();

        $this->assertCount(2, Experiment::all());
        $this->assertCount(4, Goal::all());

        $this->artisan('ab:flush');

        $this->assertCount(0, Experiment::all());
        $this->assertCount(0, Goal::all());
    }
}
