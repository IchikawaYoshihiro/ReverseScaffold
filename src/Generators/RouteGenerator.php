<?php
namespace Ichikawayac\ReverseScaffoldGenerator\Generators;

class RouteGenerator extends BaseGenerator
{
    public function exists()
    {
		try {
            route("{$this->getRouteFullName('.')}.index");
            return true;
		} catch(\Throwable $e) {
			return false;
		}
    }

    public function generate()
    {
		$replaces = [
            'DummyRoutePrefix'        => $this->getRouteDottedPrefix(),
            'DummyRouteName'          => $this->getRouteFullName(),
			'DummyControllerFullName' => $this->getControllerRefarenceName('App\\Http\\Controllers\\'),
		];

		$stub = static::getStubFile('routes.stub');

		static::fileAppend($stub, $replaces, $this->getGenerateFilePath());
    }

    public function getGenerateFilePath()
    {
        return $this->getRouteFilePath();
    }

    /**
     * return dot added route prefix
     * eg /admin/foo_bar/index -> admin.
     * @return string
     */
    private function getRouteDottedPrefix()
    {
        $route = $this->getRoutePrefix('.');
        if ($route) {
            return $route.'.';
        }
    }

    public function overwriteMessgae()
    {
        return $this->getRouteFullName('.').' is defined. Do you add route?';
    }

    public function generatedMessage()
    {
        return '[modified]  '.$this->getGenerateFilePath();
    }
}
