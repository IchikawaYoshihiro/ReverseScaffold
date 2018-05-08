<?php
namespace Ichikawayac\ReverseScaffoldGenerator\Generators;

class ModelGenerator extends BaseGenerator
{
    public function generate()
    {
        $replaces = [
            'DummyNameSpace' => $this->getModelNamespace(),
            'DummyName'      => $this->getModelName(),
            'DummyColumns'   => $this->getFillableFields()->pluck('Field')->implode("',\n\t\t'"),
            'DummyCasts'     => $this->buildCasts(),
        ];

        $stub = static::getStubFile('model.stub');

        $this->fileGenerate($stub, $replaces, $this->getGenerateFilePath());
    }

    public function getGenerateFilePath()
    {
        return base_path($this->getModelFullName().'.php');
    }

    public function buildCasts()
    {
        return $this->getFillableFields()
            ->filter(function($item) {
                return static::isDateTime($item);
            })
            ->map(function($item) {
                return "'{$item->Field}' => '{$this->judgeCastType($item)}'";
            })
            ->implode(",\n\t\t");
    }

    public function judgeCastType($item)
    {
        if (static::isDateTime($item)) {
            return 'datetime';
        }
        if (static::isBoolean($item)) {
            return 'boolean';
        }
    }
}
