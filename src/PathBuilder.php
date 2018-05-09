<?php
namespace Ichikawayac\ReverseScaffoldGenerator;

class PathBuilder
{
    private $model_pathes = [];
    private $controller_pathes = [];
    private $view_pathes  = [];
    private $route_pathes = [];
    private $lang_pathes  = [];

    public $valiables_name;
    public $valiable_name;


    public function __construct($name)
    {
        // input :foo_bar

        // $foo_bars
        $this->valiables_name = snake_case(str_plural($name));
        // $foo_bar
        $this->valiable_name  = snake_case(str_singular($name));

        // FooBar
        $this->model_pathes = ['App', studly_case(str_singular($name))];

        // FooBarController
        $this->controller_pathes =[ 'App', 'Http', 'Controllers', $this->getModelName().'Controller'];

        // [foo_bar]
        $this->view_pathes = [$this->valiable_name];

        // [foo_bar]
        $this->route_pathes = [$this->valiable_name];

        // [foo_bar]
        $this->lang_pathes = [$this->valiable_name];
    }


    /*
    |--------------------------------------------------------------------------
    | real pathes
    |--------------------------------------------------------------------------
    */
    public function getModelFilePath()
    {
        return static::fixPath(base_path($this->getModelFullName().'.php'));
    }
    public function getControllerFilePath()
    {
        return static::fixPath(base_path($this->getControllerFullName().'.php'));
    }
    public function getViewFilePath($name)
    {
        return static::fixPath(resource_path('views/'.$this->getViewFullName().'/'.$name.'.blade.php'));
    }
    public function getLangFilePath()
    {
        return static::fixPath(resource_path('lang/en/'.$this->getLangFullName().'/message.php'));
    }
    public function getRouteFilePath()
    {
        return static::fixPath(base_path('routes/web.php'));
    }


    /*
    |--------------------------------------------------------------------------
    | Model
    |--------------------------------------------------------------------------
    */
    public function getModelName()
    {
        return last($this->model_pathes);
    }
    public function getModelFullName()
    {
        return implode('\\', $this->model_pathes);
    }
    public function getModelNamespace()
    {
        $path = $this->model_pathes;
        array_pop($path);
        return implode('\\', $path);
    }
    public function setModelPath($path)
    {
        $this->model_pathes = array_merge(['App'], static::explode($path));
    }


    /*
    |--------------------------------------------------------------------------
    | Controller
    |--------------------------------------------------------------------------
    */
    public function getControllerName()
    {
        return last($this->controller_pathes);
    }
    public function getControllerFullName()
    {
        return implode('\\', $this->controller_pathes);
    }
    public function getControllerNamespace()
    {
        $path = $this->controller_pathes;
        array_pop($path);
        return implode('\\', $path);
    }
    public function setControllerPath($path)
    {
        $this->controller_pathes = array_merge(['App', 'Http', 'Controllers'], static::explode($path));
    }
    public function getControllerRefarenceName($base)
    {
        return str_replace($base, '', $this->getControllerFullName());
    }


    /*
    |--------------------------------------------------------------------------
    | View
    |--------------------------------------------------------------------------
    */
    public function getViewName()
    {
        return last($this->view_pathes);
    }
    public function getViewFullName($sepalactor = '/')
    {
        return implode($sepalactor, $this->view_pathes);
    }
    public function setViewPath($path)
    {
        $this->view_pathes = static::explode($path);
    }


    /*
    |--------------------------------------------------------------------------
    | Route
    |--------------------------------------------------------------------------
    */
    public function getRouteName()
    {
        return last($this->route_pathes);
    }
    public function getRoutePrefix($sepalactor = '/')
    {
        $path = $this->route_pathes;
        array_pop($path);
        return implode($sepalactor, $path);
    }
    public function getRouteFullName($sepalactor = '/')
    {
        return implode($sepalactor, $this->route_pathes);
    }
    public function setRoutePath($path)
    {
        $this->route_pathes = static::explode($path);
    }


    /*
    |--------------------------------------------------------------------------
    | Lang
    |--------------------------------------------------------------------------
    */
    public function getLangName()
    {
        return last($this->lang_pathes);
    }
    public function getLangFullName($sepalactor = '/')
    {
        return implode($sepalactor, $this->lang_pathes);
    }
    public function setLangPath($path)
    {
        $this->lang_pathes = static::explode($path);
    }
    public function __(...$names)
    {
        $name = implode('.', $names);
        return "__('{$this->getLangFullName()}/message.{$name}')";
    }


    private static function explode($str)
    {
        return explode('/', str_replace(['.', '\\'], '/', $str));
    }
    private static function fixPath($str)
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $str);
    }
}
