<?php

namespace Ichikawayac\ReverseScaffoldGenerator;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ichikawayac\ReverseScaffoldGenerator\Exceptions\TableNotFoundException;
use Ichikawayac\ReverseScaffoldGenerator\GeneratorFactory;

class ReverseScaffoldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:reverse {name : The target table name.} {--f|force : Force over write files.}';

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
        $overwrite = $this->option('force', true);

        $this->info("Target table is '{$table_name}'");

        try {
            $columns = static::fetchColumns($table_name);
        } catch(TableNotFoundException $e) {
            $this->error($e->getMessage());
            exit();
        }

        $pb = new PathBuilder($table_name);

        // model name
        $pb->setModelPath($this->ask('input the Model name', 'Models\\'.$pb->getModelName()));

        // controller name
        $pb->setControllerPath($this->ask('input the Controller name', 'Admin\\'.$pb->getControllerName()));

        // view dir name
        $pb->setViewPath($this->ask('input the View dirctory name', 'admin/'.$pb->getViewName()));

        // route name
        $pb->setRoutePath($this->ask('input the Route name', 'admin/'.$pb->getRouteName()));

        // route name
        $pb->setLangPath($this->ask('input the Lang name', 'admin/'.$pb->getLangName()));

        $factory = new GeneratorFactory($columns, $pb);

        foreach(GeneratorFactory::geteratorList() as $generator_name) {
            $gen = $factory->create($generator_name);

            if ($overwrite || !$gen->exists() || $this->confirm($gen->confirmMessgae())) {
                $gen->generate();
                $this->info($gen->generatedMessage());
            } else {
                $this->error($gen->skippedMessage());
            }
        }
    }

    /**
     * check the table name exists
     * @return bool exists
     */
    private static function existsTable($name)
    {
        return in_array($name, static::fetchTables(), true);
    }


    /**
     * fetch table name list
     * @return array table name list
     */
    private static function fetchTables()
    {
        $tables = DB::select('show tables');

        return array_map(function($table) {
            $tmp = (array)$table;
            return array_shift($tmp);
        }, $tables);
    }

    /**
     * fetch column field information
     * @return Illuminate\Database\Eloquent\Collection
     */
    protected function fetchColumns($name)
    {
        if (static::existsTable($name)) {
            $columns = DB::select('show columns from '.$name);
            return collect($columns);
        } else {
            throw new TableNotFoundException;
        }
    }
}
