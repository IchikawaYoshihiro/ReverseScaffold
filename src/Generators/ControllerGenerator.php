<?php
namespace Ichikawayac\ReverseScaffoldGenerator\Generators;

class ControllerGenerator extends BaseGenerator
{
    public function generate()
    {
        $replaces = [
            'DummyControllerNamespace' => $this->getControllerNamespace(),
            'DummyController'          => $this->getControllerName(),
            'DummyModelFullName'       => $this->getModelFullName(),
            'DummyModel'               => $this->getModelName(),
            'DummyViewName'            => $this->getViewFullName(),
            'DummyRouteName'           => $this->getRouteFullName('.'),
            'DummyLangName'            => $this->getLangFullName(),

            'DummyValiables'  => $this->valiables_name,
            'DummyValiable'   => $this->valiable_name,
            'DummyColumns'    => $this->getFillableFields()->pluck('Field')->implode("',\n            '"),
            'DummyValidator'  => $this->buildValidator(),
        ];

        $stub = static::getStubFile('controller.stub');

        static::fileGenerate($stub, $replaces, $this->getGenerateFilePath());
    }

    public function getGenerateFilePath()
    {
        return static::fixPath(base_path($this->getControllerFullName().'.php'));
    }


    protected function buildValidator()
    {
        return $this->getFillableFields()->map(function ($item) {
            $rule = static::buildRule($item);
            return "'{$item->Field}' => '{$rule}'";
        })->implode(",\n            ");
    }


    protected static function buildRule($item)
    {
        $rules = [];

        // not null?
        if (static::is($item->Null, 'YES') || static::isBoolean($item)) {
            $rules[] = 'nullable';
        } else {
            $rules[] = 'required';
        }

        // types
        if (static::isBoolean($item)) {
            $rules[] = 'boolean';
        }
        else {
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
        }

        return implode($rules, '|');
    }

    protected static function buildLength($field)
    {
        preg_match('/\((\d+)\)/', $field, $matches);
        return 'max:'.$matches[1] ?? '255';
    }
}
