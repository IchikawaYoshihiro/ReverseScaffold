<?php

namespace yosichikaw\ReverseScaffold;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use \Artisan;

class Generator
{
    /**
     * @var string
     */
    protected $table_name;

    /**
     * @var string
     */
    protected $model_name;

    /**
     * @var Illuminate\Database\Eloquent\Collection
     */
    protected $columns;

    public function __construct($name)
    {
        $name = trim($name);

        $this->table_name    = str_plural($name);
        $this->valiable_name = str_singular(mb_strtolower($name));
        $this->model_name    = Str::studly($this->valiable_name);

        if ($this->existsTable()) {
            $this->fetchColumns();
        } else {
            throw new Exception("Table '{$this->table_name}' not found!");
        }
    }


    /**
     * check the table name exists
     * @return bool exists
     */
    public function existsTable()
    {
        return in_array($this->table_name, $this->fetchTables(), true);
    }


    /**
     * generate model file
     */
    public function generateModel()
    {
        $replaces = [
            'DummyModel'   => $this->model_name,
            'DummyColumns' => $this->fillableColumns()->pluck('Field')->implode("',\n\t\t'"),
        ];

        $stub     = __DIR__.'/stubs/model.stub';
        $filename = $this->model_name.'.php';
        $path     = app_path($filename);

        $this->fileGenerate($stub, $replaces, $path);
    }


    /**
     * generate controller file
     */
    public function generateController()
    {
        $replaces = [
            'DummyController' => $this->getControllerName(),
            'DummyModel'      => $this->model_name,
            'DummyValiables'  => str_plural($this->valiable_name),
            'DummyValiable'   => str_singular($this->valiable_name),
            'DummyViewDir'    => str_plural($this->valiable_name),
            'DummyColumns'    => $this->fillableFields()->pluck('Field')->implode("',\n\t\t\t'"),
            'DummyValidator'  => $this->validator(),
        ];

        $stub     = __DIR__.'/stubs/controller.stub';
        $filename = $this->getControllerName().'.php';
        $path     = app_path('Http/Controllers/'.$filename);

        $this->fileGenerate($stub, $replaces, $path);
    }


    /**
     * add resources route
     */
    public function addRoute()
    {
        $replaces = [
            'DummyController'=> $this->getControllerName(),
            'DummyUrl'       => str_singular($this->valiable_name),
        ];

        $stub = __DIR__.'/stubs/routes.stub';
        $path = base_path('routes/web.php');

        $this->fileAppend($stub, $replaces, $path);
    }

    /**
     * generate view files
     */
    public function generateViews()
    {
        
    }


    protected function fileGenerate($stub, $replaces, $path)
    {
        $file = file_get_contents($stub);
        $file = str_replace(array_keys($replaces), array_values($replaces), $file);
        return file_put_contents($path, $file);
    }

    
    protected function fileAppend($stub, $replaces, $path)
    {
        $file = file_get_contents($stub);
        $file = str_replace(array_keys($replaces), array_values($replaces), $file);
        return file_put_contents($path, $file, FILE_APPEND);
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

    protected function fillableFields()
    {
        return $this->columns
            ->filter(function($column) {
                return !in_array($column->Field, static::getBlackListColumns(), true);
            });
    }

    protected function validator()
    {
        return $this->fillableFields()->map(function ($item) {
            $rule = static::buildRule($item);
            return "'{$item->Field}' => '{$rule}'";
        })->implode(",\n\t\t\t");
    }


    protected static function buildRule($item)
    {
        $rules = [];

        // not null?
        if ($item->Null === 'NO') {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // types
        if (static::has($item->Type, 'int')) {
            $rules[] = 'numeric';
        }

        if (static::has($item->Type, 'char') || static::has($item->Type, 'text')) {
            $rules[] = 'string';
            
            if (static::has($item->Type, '(')) {
                $rules[] = static::buildLength($item->Type);
            }
        }

        if (static::has($item->Type, 'unsigned')) {
            $rules[] = 'min:0';
        }

        // special name
        if (static::has($item->Field, 'email')) {
            $rules[] = 'email';
        }

        return implode($rules, '|');
    }
    private static function has($haystack, $needle)
    {
        return stripos($haystack, $needle) !== false;
    }

    protected static function buildLength($field)
    {
        preg_match('/\((\d+)\)/', $field, $matches);
        return 'max:'.$matches[1] ?? '255';
    }
    

    /**
     * not fillable columns
     * @return array
     */
    protected static function getBlackListColumns()
    {
        return ['id', 'created_at', 'updated_at'];
    }
}
