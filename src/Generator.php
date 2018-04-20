<?php

namespace yosichikaw\ReverseScaffold;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use \Artisan;

class Generator
{
    /**
     * eg. foo_bars
     * @var string
     */
    protected $table_name;

    /**
     * eg. FooBar
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
            'DummyColumns' => $this->fillableFields()->pluck('Field')->implode("',\n\t\t'"),
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

        static::fileGenerate($stub, $replaces, $path);
    }


    /**
     * add resources route
     */
    public function addRoute()
    {
        $replaces = [
            'DummyController'=> $this->getControllerName(),
            'DummyValiables' => str_plural($this->valiable_name),
        ];

        $stub = __DIR__.'/stubs/routes.stub';
        $path = base_path('routes/web.php');

        static::fileAppend($stub, $replaces, $path);
    }

    /**
     * generate view files
     */
    public function generateViews()
    {
        // make views/{modelname} dir
        $dirname = str_plural($this->valiable_name);
        $path = resource_path("views/{$dirname}");
        static::mkdir($path);

        // make views/layouts dir
        $path = resource_path("views/layouts");
        static::mkdir($path);


        $replaces = [
            'DummyValiables' => str_plural($this->valiable_name),
            'DummyValiable'  => $this->valiable_name,
            'DummyModel'     => $this->model_name,
            'DummyTableHead' => $this->generateTableHead(),
            'DummyTableBody' => $this->generateTableBody(),
            'DummyList'      => $this->generateList(),
            'DummyInputArea' => $this->generateInputArea(),
        ];
        
        // layouts
        $stub = __DIR__.'/stubs/views/layouts/app.blade.stub';
        $path = resource_path("views/layouts/app.blade.php");
        static::fileGenerate($stub, $replaces, $path);
        
        // index
        $stub = __DIR__.'/stubs/views/index.blade.stub';
        $path = resource_path("views/{$dirname}/index.blade.php");
        static::fileGenerate($stub, $replaces, $path);

        $stub = __DIR__.'/stubs/views/create.blade.stub';
        $path = resource_path("views/{$dirname}/create.blade.php");
        static::fileGenerate($stub, $replaces, $path);

        $stub = __DIR__.'/stubs/views/edit.blade.stub';
        $path = resource_path("views/{$dirname}/edit.blade.php");
        static::fileGenerate($stub, $replaces, $path);

        $stub = __DIR__.'/stubs/views/_form.blade.stub';
        $path = resource_path("views/{$dirname}/_form.blade.php");
        static::fileGenerate($stub, $replaces, $path);

        $stub = __DIR__.'/stubs/views/show.blade.stub';
        $path = resource_path("views/{$dirname}/show.blade.php");
        static::fileGenerate($stub, $replaces, $path);
    }

    public function addLang()
    {
        $langs;
    }
    

    protected function generateTableHead()
    {
        return $this->fillableFields()->map(function($column) {
            return "<th>{{ __('message.{$this->valiable_name}.{$column->Field}') }}</th>";
        })->implode("\n\t\t\t");
    }


    protected function generateTableBody()
    {
        return $this->fillableFields()->map(function($column) {
            return "<td>{{ \${$this->valiable_name}->{$column->Field} }}</td>";
        })->implode("\n\t\t\t");
    }


    protected function generateList()
    {
        return $this->fillableFields()->map(function($column) {
            return "<dt>{{ __('message.{$this->valiable_name}.{$column->Field}') }}</dt>"
                ."<dd>{{ \${$this->valiable_name}->{$column->Field} }}</dd>";
        })->implode("\n\t\t");
    }

    protected function generateInputArea()
    {
        return $this->fillableFields()->map(function($item) {
            $input = $this->buildInput($item);
            $requiured = $item->Null === 'NO' ? "<span class=\"badge badge-danger\">{{ __('message.required') }}</span>" : '';
            return <<< EOM
<div class="form-group">
    <label for="{$item->Field}">{$requiured} {{ __('message.{$this->valiable_name}.{$item->Field}') }}</label>
    {$input}
    @if (\$errors->has('{$item->Field}'))
        <div class="alert alert-danger">
            <ul>
                @foreach (\$errors->get('{$item->Field}') as \$error)
                    <li>{{ \$error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
EOM;
        })->implode("\n\n");
        /*

*/
    }
    protected function buildInput($item)
    {
        if (static::has($item->Type, 'text')) {
            return <<< EOM
<textarea name="{$item->Field}" id="{$item->Field}" class="form-control">{{ old('{$item->Field}', $user->ddd ?? '') }}</textarea>
EOM;
        } else {
            $type = static::judgeType($item);
            return <<< EOM
<input type="{$type}" name="{$item->Field}" id="{$item->Field}" class="form-control" value="{{ old('{$item->Field}', \${$this->valiable_name}->{$item->Field} ?? '') }}">
EOM;
        }
    }
    protected static function judgeType($item)
    {
        // special
        if (static::has($item->Field, 'email')) {
            return 'email';
        }
        if (static::has($item->Field, 'password')) {
            return 'password';
        }

        // types
        if (static::has($item->Type, 'int')) {
            return 'number';
        }
        if (static::has($item->Type, 'char') || static::has($item->Type, 'text')) {
            return 'text';
        }
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
