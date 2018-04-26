<?php
namespace Ichikawayac\ReverseScaffoldGenerator\Traits;

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

		$stub = static::getStubFile('model.stub');

		$this->fileGenerate($stub, $replaces, $this->modelFilePath());
	}

	public function modelFileExists()
	{
		return file_exists($this->modelFilePath());
	}

	public function modelFilePath()
	{
		return app_path($this->ModelName.'.php');
	}
}
