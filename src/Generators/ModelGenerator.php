<?php
namespace Ichikawayac\ReverseScaffoldGenerator\Generators;

class ModelGenerator extends BaseGenerator
{
    public function generate()
    {
        $replaces = [
            'DummyNameSpace' => $this->getModelNamespace(),
            'DummyName'      => $this->getModelName(),
            'DummyColumns'   => $this->getFillableFields()->pluck('Field')->implode("',\n        '"),
        ];

        $stub = static::getStubFile('model.stub');

        $this->fileGenerate($stub, $replaces, $this->getGenerateFilePath());
    }

    public function getGenerateFilePath()
    {
        return $this->getModelFilePath();
    }
}
