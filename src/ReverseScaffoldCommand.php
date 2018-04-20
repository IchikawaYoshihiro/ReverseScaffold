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
    protected $signature = 'make:reverse {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reverse scaffolding from database';

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

        $gen = new Generator($table_name);

        echo "generate model...\n";
        $gen->generateModel();

        echo "generate controller...\n";
        $gen->generateController();

        echo "add route...\n";
        // $gen->addRoute();

        echo "generate view files...\n";
        $gen->generateViews();
    }
}
