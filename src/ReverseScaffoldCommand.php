<?php

namespace yosichikaw\ReverseScaffold;

use Illuminate\Console\Command;

class ReverseScaffoldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scaffold:reverse {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'reverse scaffolding from database';

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
        $table_name = $this->argument('name');

        $gen = new ReverseScaffoldGenerator($table_name);

        $gen->generate();
    }
}
