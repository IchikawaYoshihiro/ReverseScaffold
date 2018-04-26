<?php

namespace Ichikawayac\ReverseScaffoldGenerator;

use Illuminate\Console\Command;

class ReverseScaffoldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:reverse {name : Table or model name.} {--f|force : Force over write files.}';

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
        $overwrite = $this->option('force');

        $gen = new Generator($table_name);

        $this->info("Generate model {$gen->ModelName}...");
        if ($overwrite || !$gen->modelFileExists()) {
            $gen->generateModel();
        } else {
            $this->error('File exists skipped!');
        }

        $this->info("Generate controller {$gen->ControllerName}...");
        if ($overwrite || !$gen->controllerFileExists()) {
            $gen->generateController();
        } else {
            $this->error('File exists skipped!');
        }

        $this->info('Add routes...');
        if ($overwrite || !$gen->routeDefined()) {
            $gen->addRoute();
        } else {
            $this->error('Route already defined skipped!');
        }

        $this->info("Generate {$gen->valiables_name} view files...");
        if ($overwrite || !$gen->viewFileExists()) {
            $gen->generateViews();
        } else {
            $this->error('File exists skipped!');
        }

        $this->info('Generate lang file...');
        if ($overwrite || !$gen->langFileExists()) {
            $gen->generateLang();
        } else {
            $this->error('File exists skipped!');
        }
    }
}
