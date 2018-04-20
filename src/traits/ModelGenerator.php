<?php
namespace yosichikaw\ReverseScaffold\Traits;

trait ModelGenerator {

    /**
     * generate model file
     */
    public function generateModel()
    {
        $replaces = [
            'DummyModel'   => $this->ModelName,
            'DummyColumns' => $this->fillableFields()->pluck('Field')->implode("',\n\t\t'"),
        ];

        $stub     = static::getStubFile('model.stub');
        $filename = $this->ModelName.'.php';
        $path     = app_path($filename);

        $this->fileGenerate($stub, $replaces, $path);
    }
}
