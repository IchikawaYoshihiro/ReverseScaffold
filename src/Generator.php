<?php

namespace IchikawaYoshihiro\ReverseScaffoldGenerator;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use \Artisan;

class Generator
{
    use Traits\RouteGenerator;
    use Traits\ModelGenerator;
    use Traits\ControllerGenerator;
    use Traits\ViewGenerator;

    /**
     * @var Illuminate\Database\Eloquent\Collection
     */
    protected $columns;

    public $ModelName;
    public $ControllerName;
    public $valiables_name;
    public $valiable_name;


    public function __construct($name)
    {
        // input :foo_bar

        // FooBar
        $this->ModelName = studly_case(str_singular($name));
        // FooBarController
        $this->ControllerName = $this->ModelName.'Controller';
        // $foo_bars
        $this->valiables_name = str_plural($name);
        // $foo_bar
        $this->valiable_name  = str_singular($name);
        
        if ($this->existsTable()) {
            $this->fetchColumns();
        } else {
            throw new Exception("Table '{$this->valiables_name}' not found!");
        }
    }



    public function generateLang()
    {
        $langs = [
            'crud' => [
                'save' => 'Save',
                'create' => 'Create',
                'show' => 'Show',
                'edit' => 'Edit',
                'delete' => 'Delete',
                'confirm' => 'Are you sure?',
                'created' => 'Created',
                'updated' => 'Updated',
                'deleted' => 'Deleted',
                'required' => 'Required',
            ],
            $this->valiable_name => $this->buildLangColumn(),
        ];
        $path = resource_path('lang/en/message.php');

        $replaces = [
            'DummyLangs' => var_export($this->mergeLang($path, $langs), true),
        ];
        $stub = static::getStubFile('message.stub');

        $this->fileGenerate($stub, $replaces, $path);
    }

    protected function buildLangColumn()
    {
        $name  = static::toTitle($this->valiable_name);
        $names = static::toTitle($this->valiables_name);

        $base = [
            'id' => 'ID',
            'action' => 'Action',
            'create' => "Create new {$name}",
            'show' => "Show {$name}",
            'edit' => "Edit {$name}",
            'list' => "List of {$names}",
        ];

        $columns = $this->fillableFields()->pluck('Field')->reduce(function($carry, $item) {
            $carry[$item] = static::toTitle($item);
            return $carry;
        }, []);

        return array_replace_recursive ($base, $columns);
    }
    protected static function toTitle($str)
    {
        return title_case(str_replace(['_', '-'], ' ', $str));
    }


    protected static function mkdir($path)
    {
        if (!file_exists($path)) {
            mkdir($path, true);
        }
    }


    protected static function fileGenerate($stub, $replaces, $path)
    {
        $file = file_get_contents($stub);
        $file = str_replace(array_keys($replaces), array_values($replaces), $file);
        return file_put_contents($path, $file);
    }

    
    protected static function fileAppend($stub, $replaces, $path)
    {
        $file = file_get_contents($stub);
        $file = str_replace(array_keys($replaces), array_values($replaces), $file);
        return file_put_contents($path, $file, FILE_APPEND);
    }

    
    protected static function mergeLang($path, $langs)
    {
        $current = [];
        if (file_exists($path)) {
            $current = require($path);
        }
        return array_replace_recursive  ($current, $langs);
    }


    protected static function getStubFile($path)
    {
        return __DIR__.'/stubs/'.$path;
    }

    /**
     * check the table name exists
     * @return bool exists
     */
    protected function existsTable()
    {
        return in_array($this->valiables_name, $this->fetchTables(), true);
    }

    /**
     * fetch table name list
     * @return array table name list
     */
    protected function fetchTables()
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
    protected function fetchColumns()
    {
        $columns = DB::select('show columns from '.$this->valiables_name);

        $this->columns = collect($columns);
    }

    protected function fillableFields()
    {
        return $this->columns
            ->filter(function($column) {
                return !in_array($column->Field, static::autoFilledColumns(), true);
            });
    }

    private static function has($haystack, $needle)
    {
        return stripos($haystack, $needle) !== false;
    }

    /**
     * not fillable columns
     * @return array
     */
    protected static function autoFilledColumns()
    {
        return ['id', 'created_at', 'updated_at'];
    }
}
