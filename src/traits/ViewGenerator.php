<?php
namespace IchikawaYoshihiro\ReverseScaffoldGenerator\Traits;

trait ViewGenerator {

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
            'DummyValiables' => $this->valiables_name,
            'DummyValiable'  => $this->valiable_name,
            'DummyModel'     => $this->ModelName,
            'DummyTableHead' => $this->generateTableHead(),
            'DummyTableBody' => $this->generateTableBody(),
            'DummyList'      => $this->generateList(),
            'DummyInputArea' => $this->generateInputArea(),
        ];
        
        // layouts
        $stub = static::getStubFile('views/layouts/app.blade.stub');
        $path = resource_path("views/layouts/app.blade.php");
        static::fileGenerate($stub, $replaces, $path);
        
        // index
        $stub = static::getStubFile('views/index.blade.stub');
        $path = resource_path("views/{$dirname}/index.blade.php");
        static::fileGenerate($stub, $replaces, $path);

        $stub = static::getStubFile('views/create.blade.stub');
        $path = resource_path("views/{$dirname}/create.blade.php");
        static::fileGenerate($stub, $replaces, $path);

        $stub = static::getStubFile('views/edit.blade.stub');
        $path = resource_path("views/{$dirname}/edit.blade.php");
        static::fileGenerate($stub, $replaces, $path);

        $stub = static::getStubFile('views/_form.blade.stub');
        $path = resource_path("views/{$dirname}/_form.blade.php");
        static::fileGenerate($stub, $replaces, $path);

        $stub = static::getStubFile('views/show.blade.stub');
        $path = resource_path("views/{$dirname}/show.blade.php");
        static::fileGenerate($stub, $replaces, $path);
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
    }


    protected function buildInput($item)
    {
        if (static::has($item->Type, 'text')) {
            return <<< EOM
<textarea name="{$item->Field}" id="{$item->Field}" class="form-control">{{ old('{$item->Field}', \${$this->valiable_name}->{$item->Field} ?? '') }}</textarea>
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
}
