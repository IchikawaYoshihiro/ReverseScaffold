<?php
namespace Ichikawayac\ReverseScaffoldGenerator;

use Ichikawayac\ReverseScaffoldGenerator\Generators\ModelGenerator;
use Ichikawayac\ReverseScaffoldGenerator\Generators\ControllerGenerator;
use Ichikawayac\ReverseScaffoldGenerator\Generators\ViewIndexGenerator;
use Ichikawayac\ReverseScaffoldGenerator\Generators\ViewCreateGenerator;
use Ichikawayac\ReverseScaffoldGenerator\Generators\ViewEditGenerator;
use Ichikawayac\ReverseScaffoldGenerator\Generators\ViewShowGenerator;
use Ichikawayac\ReverseScaffoldGenerator\Generators\ViewFormGenerator;
use Ichikawayac\ReverseScaffoldGenerator\Generators\ViewLayoutGenerator;
use Ichikawayac\ReverseScaffoldGenerator\Generators\RouteGenerator;
use Ichikawayac\ReverseScaffoldGenerator\Generators\LangGenerator;

class GeneratorFactory
{
    private $columns;
    private $path_builder;

    public function __construct($columns, $path_builder)
    {
        $this->columns = $columns;
        $this->path_builder = $path_builder;
    }

    /**
     * "generator function" for generator
     * @return Generator
     */
    public function generators()
    {
        foreach(static::geteratorList() as $generator) {
            yield new $generator($this->columns, $this->path_builder);
        }
    }

    /**
     * list of generators
     * @return array
     */
    protected static function geteratorList()
    {
        return [
            ModelGenerator::class,
            ControllerGenerator::class,
            ViewIndexGenerator::class,
            ViewCreateGenerator::class,
            ViewEditGenerator::class,
            ViewShowGenerator::class,
            ViewFormGenerator::class,
            ViewLayoutGenerator::class,
            LangGenerator::class,
            RouteGenerator::class,
        ];
    }


}
