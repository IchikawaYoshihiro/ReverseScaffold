<?php
namespace Ichikawayac\ReverseScaffoldGenerator\Traits;

trait RouteGenerator {

	/**
	 * add resources route
	 */
	public function addRoute()
	{
		$replaces = [
			'DummyController' => $this->ControllerName,
			'DummyValiables'  => $this->valiables_name,
		];

		$stub = static::getStubFile('routes.stub');
		$path = base_path('routes/web.php');

		static::fileAppend($stub, $replaces, $path);
	}

	public function routeDefined()
	{
		try {
			return !!route("$this->valiables_name.index");
		} catch(\Throwable $e) {
			return false;
		}
	}
}
