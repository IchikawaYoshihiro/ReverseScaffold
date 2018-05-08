<?php
namespace Ichikawayac\ReverseScaffoldGenerator\Generators;

class ViewBaseGenerator extends BaseGenerator
{
    protected $view_name;

    public function generate()
    {
        $replaces = [
            'DummyValiables' => $this->valiables_name,
            'DummyValiable'  => $this->valiable_name,
            'DummyTableHead' => $this->generateTableHead(),
            'DummyTableBody' => $this->generateTableBody(),
            'DummyList'      => $this->generateList(),
            'DummyInputArea' => $this->generateInputArea(),
            'DummyViewName'  => $this->getViewFullName('.'),
            'DummyRouteName' => $this->getRouteFullName('.'),
            'DummyLangName'  => $this->getLangFullName(),
        ];

        $stub = static::getStubFile('views/'.$this->view_name.'.blade.stub');

        static::fileGenerate($stub, $replaces, $this->getGenerateFilePath());
    }


    public function getGenerateFilePath()
    {
        return resource_path('views/'.$this->getViewFullName().'/'.$this->view_name.'.blade.php');
    }


    /**
     * <th>{{ __('lang_prefix.message.foo.bar') }}</th>
     * ...
     */
    protected function generateTableHead()
    {
        return $this->getFillableFields()->map(function($column) {
            return "<th>{{ __('{$this->getLangFullName()}/message.{$this->valiable_name}.{$column->Field}') }}</th>";
        })->implode("\n\t\t\t");
    }


    /**
     * <td>{{ $foo->bar }}</td>
     * ...
     */
    protected function generateTableBody()
    {
        return $this->getFillableFields()->map(function($column) {
            // hide password
            if(static::has($column->Field, 'password')) {
                return "<td>******</td>";
            }
            return "<td>{{ \${$this->valiable_name}->{$column->Field} }}</td>";
        })->implode("\n\t\t\t");
    }


    /**
     * <dt>{{ __('lang_prefix.message.foo.bar') }}</dt><dd>{{ $foo->bar }}</dd>
     * ...
     */
    protected function generateList()
    {
        return $this->getFillableFields()->map(function($column) {
            // hide password
            if(static::has($column->Field, 'password')) {
                return "<dt>{{ __('{$this->getLangFullName()}/message.{$this->valiable_name}.{$column->Field}') }}</dt>"
                    ."<dd>******</dd>";
            }

            return "<dt>{{ __('{$this->getLangFullName()}/message.{$this->valiable_name}.{$column->Field}') }}</dt>"
                ."<dd>{{ \${$this->valiable_name}->{$column->Field} }}</dd>";
        })->implode("\n\t\t");
    }

    /**
     *  <div class="form-group">
     *      <label for="bar"><span class="badge badge-danger">{{ __('lang_prefix.message.required') }}</span> {{ __('lang_prefix.message.foo.bar') }}</label>
     *      <input type="text" name="bar" id="bar" class="form-control" value="{{ old('bar', $foo->bar ?? '') }}">
     *      <div class="alert alert-danger">
     *          <ul>
     *              <li>error message</li>
     *          </ul>
     *      </div>
     *  </div>
     *  ...
     */
    protected function generateInputArea()
    {
        return $this->getFillableFields()->map(function($item) {
            $input = $this->buildInput($item);
            $requiured = $item->Null === 'NO'
                ? "<span class=\"badge badge-danger\">{{ __('{$this->getLangFullName()}/message.crud.required') }}</span>"
                : "<span class=\"badge badge-info\">{{ __('{$this->getLangFullName()}/message.crud.optional') }}</span>";
            return <<< EOM
<div class="form-group">
    <label for="{$item->Field}">{$requiured} {{ __('{$this->getLangFullName()}/message.{$this->valiable_name}.{$item->Field}') }}</label>
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
        }
        if (static::isBoolean($item)) {
            return <<< EOM
<input type="checkbox" name="{$item->Field}" id="{$item->Field}" class="" {{ old('{$item->Field}', \${$this->valiable_name}->{$item->Field} ?? false) ? 'checked' : ''}}>
EOM;
        }

        return <<< EOM
<input type="{$this->judgeType($item)}" name="{$item->Field}" id="{$item->Field}" class="form-control" value="{$this->judgeValue($item)}">
EOM;
    }

    protected function judgeType($item)
    {
        // special
        if (static::has($item->Field, 'email')) {
            return 'email';
        }
        if (static::isBoolean($item)) {
            return 'checkbox';
        }

        // types
        if (static::has($item->Type, 'int')) {
            return 'number';
        }
        if (static::is($item->Type, 'datetime')) {
            return 'datetime-local';
        }
        if (static::is($item->Type, 'time')) {
            return 'time';
        }
        if (static::is($item->Type, 'date')) {
            return 'date';
        }
        return 'text';
    }

    protected function judgeValue($item)
    {
        // special
        if (static::has($item->Field, 'password')) {
            return '';
        }
        if (static::isDateTime($item)) {
            return "{{ old('{$item->Field}', optional(\${$this->valiable_name}->{$item->Field} ?? null)->format('Y-m-d\TH:i')) }}";
        }

        return "{{ old('{$item->Field}', \${$this->valiable_name}->{$item->Field} ?? '') }}";
    }

    protected function judgeAttr($item)
    {
        if (static::isBoolean($item)) {
            return "{{ old('{$item->Field}', \${$this->valiable_name}->{$item->Field} ?? false) ? 'checked' : ''}}";
        }
    }
}
