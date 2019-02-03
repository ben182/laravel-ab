<?php

namespace Ben182\AbTesting\Commands;

use Illuminate\Console\Command;
use Ben182\AbTesting\Models\Experiment;
use Ben182\AbTesting\Models\Goal;

class FlushCommand extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ab:flush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Goal::truncate();
        Experiment::truncate();
    }
}
