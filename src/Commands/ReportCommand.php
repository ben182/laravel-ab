<?php

namespace Ben182\AbTesting\Commands;

use Illuminate\Console\Command;
use Ben182\AbTesting\Models\Experiment;

class ReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ab:report';

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
        $header = [
            'Experiment',
            'Visitors',
        ];

        $header = array_merge($header, array_map(function ($item) {
            return 'Goal '.$item;
        }, config('ab-testing.goals')));

        $experiments = Experiment::all()->map(function ($item) {
            $return = [$item->name, $item->visitors];

            $goalConversations = $item->goals->pluck('hit')->map(function ($hit) use ($item) {
                $item->visitors = $item->visitors ?: 1; // prevent division by zero exception

                return $hit.' ('.($hit / $item->visitors * 100).'%)';
            });

            return array_merge($return, $goalConversations->toArray());
        });

        $this->table($header, $experiments);
    }
}
