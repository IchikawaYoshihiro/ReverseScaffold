<?php
namespace Ichikawayac\ReverseScaffoldGenerator\Traits;

trait RouteGenerator {

    /**
     * add resources route
     */
    public function addRoute()
    {
		if ($this->routeExists()) {
			echo "Route exists. skipped.\n";
			return;
		}

		$replaces = [
            'DummyController' => $this->ControllerName,
            'DummyValiables'  => $this->valiables_name,
        ];

        $stub = static::getStubFile('routes.stub');
        $path = base_path('routes/web.php');

        static::fileAppend($stub, $replaces, $path);
	}
	
	public function routeExists()
	{
        try {
            return !!route("$this->valiables_name.index");
        } catch(\Throwable $e) {
            return false;
        }
	}
}