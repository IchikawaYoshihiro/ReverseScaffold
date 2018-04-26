<?php
namespace Ichikawayac\ReverseScaffoldGenerator\Traits;

trait ControllerGenerator {

    /**
     * generate controller file
     */
    public function generateController()
    {
        $replaces = [
            'DummyController' => $this->ControllerName,
            'DummyModel'      => $this->ModelName,
            'DummyValiables'  => $this->valiables_name,
            'DummyValiable'   => $this->valiable_name,
            'DummyColumns'    => $this->fillableFields()->pluck('Field')->implode("',\n\t\t\t'"),
            'DummyValidator'  => $this->validator(),
        ];

        $stub = static::getStubFile('controller.stub');

        static::fileGenerate($stub, $replaces, $this->controllerFilePath());
    }

    public function controllerFileExists()
    {
        return file_exists($this->controllerFilePath());
    }

    public function controllerFilePath()
    {
        return app_path('Http/Controllers/'.$this->ControllerName.'.php');
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

    protected static function buildLength($field)
    {
        preg_match('/\((\d+)\)/', $field, $matches);
        return 'max:'.$matches[1] ?? '255';
    }
}
