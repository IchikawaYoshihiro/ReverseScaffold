<?php

namespace yosichikaw\ReverseScaffold;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use \Artisan;

class ReverseScaffoldGenerator
{
    protected $table_name, $model_name, $columns;

    public function __construct($name)
    {
        $name = trim($name);

        $this->table_name    = str_plural($name);
        $this->valiable_name = str_singular(mb_strtolower($name));
        $this->model_name    = Str::studly($this->valiable_name);
    }

    public function generate()
    {
        if ($this->existsTable()) {
            $this->fetchColumns();

            echo "generate model.\n";
            $this->makeModel();

            echo "generate controller.\n";
            $this->makeController();

        }


    }

    private function makeModel()
    {
        $replaces = [
            'DummyNamespace' => 'App',
            'DummyModel'     => $this->model_name,
            'DummyColumns'   => $this->fillableColumns()->implode("',\n\t\t'"),
        ];

        $stub     = __DIR__.'/stubs/model.stub';
        $filename = $this->model_name.'.php';
        $path     = app_path($filename);

        $this->fileGenerate($stub, $replaces, $path);
    }

    private function makeController()
    {
        $replaces = [
            'DummyNamespace' => 'App\Http\Controllers',
            'DummyClass'     => $this->getControllerName(),
            'DummyModel'     => $this->model_name,
            'DummyValiables' => str_plural($this->valiable_name),
            'DummyValiable'  => str_singular($this->valiable_name),
            'DummyColumns'   => $this->fillableColumns()->implode("',\n\t\t\t'"),
        ];

        $stub     = __DIR__.'/stubs/controller.stub';
        $filename = $this->getControllerName().'.php';
        $path     = app_path('Http/Controllers/'.$filename);

        $this->fileGenerate($stub, $replaces, $path);
    }


    private function fileGenerate($stub, $replaces, $path)
    {
        $file = file_get_contents($stub);
        $file = str_replace(array_keys($replaces), array_values($replaces), $file);
        return file_put_contents($path, $file);
    }

    /**
     * check the table name exists
     * @return bool exists
     */
    private function existsTable()
    {
        return in_array($this->table_name, $this->fetchTables(), true);
    }

    /**
     * fetch table name list
     * @return array table name list
     */
    private function fetchTables()
    {
        $tables = DB::select('show tables');

        return array_map(function($table) {
            $tmp = (array)$table;
            return array_shift($tmp);
        }, $tables);
    }

    /**
     * fetch column field information
     */
    private function fetchColumns()
    {
        $columns = DB::select('show columns from '.$this->table_name);

        $this->columns = collect($columns);

        dump($this->columns);
    }

    /**
     * Get controller name
     *
     * @return string
     */
    protected function getControllerName()
    {
        return $this->model_name.'Controller';
    }

    protected function fillableColumns()
    {
        return $this->columns
            ->filter(function($column) {
                return !in_array($column->Field, static::getBlackListColumns(), true);
            })
            ->pluck('Field');
    }

    /**
     * not fillable columns
     * @return array
     */
    protected static function getBlackListColumns()
    {
        return ['id', 'password', 'created_at', 'updated_at'];
    }
}
