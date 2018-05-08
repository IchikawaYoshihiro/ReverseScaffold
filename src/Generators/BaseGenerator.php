<?php
namespace Ichikawayac\ReverseScaffoldGenerator\Generators;

abstract class BaseGenerator
{
    private $columns;
    private $path_builder;

    public function __construct($columns, $path_builder)
    {
        $this->columns      = $columns;
        $this->path_builder = $path_builder;
    }

    /**
     * Generate files
     */
    abstract public function generate();


    /**
     * Get generate file full path
     * @return string
     */
    abstract public function getGenerateFilePath();


    /**
     * Exists generate target files
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->getGenerateFilePath());
    }

    /**
     * confirm message for file overwrite.
     * @return string
     */
    public function confirmMessgae()
    {
        return $this->getGenerateFilePath().' is exists. Do you overwrite it?';
    }

    /**
     * message for file generated.
     * @return string
     */
    public function generatedMessage()
    {
        return '[generated] '.$this->getGenerateFilePath();
    }

    /**
     * message for file generate skipped.
     * @return string
     */
    public function skippedMessage()
    {
        return '[skipped] '.$this->getGenerateFilePath();
    }


    protected function getFillableFields()
    {
        return $this->columns
            ->filter(function($column) {
                return !in_array($column->Field, static::autoFilledColumns(), true);
            });
    }

    protected static function fileGenerate($stub, $replaces, $path)
    {
        $file = file_get_contents($stub);
        $file = str_replace(array_keys($replaces), array_values($replaces), $file);
        static::mkdir(dirname($path));
        return file_put_contents($path, $file);
    }


    protected static function fileAppend($stub, $replaces, $path)
    {
        $file = file_get_contents($stub);
        $file = str_replace(array_keys($replaces), array_values($replaces), $file);
        static::mkdir(dirname($path));
        return file_put_contents($path, $file, FILE_APPEND);
    }


    protected static function getStubFile($path)
    {
        return __DIR__.'/../stubs/'.$path;
    }

    protected static function mkdir($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    protected static function has($haystack, $needle)
    {
        return stripos($haystack, $needle) !== false;
    }

    protected static function is($haystack, $needle)
    {
        return $haystack === $needle;
    }

    protected static function begin($haystack, $needle)
    {
        return stripos($haystack, $needle) === 0;
    }

    protected static function isBoolean($item)
    {
        return static::begin($item->Field, 'is_')
            || static::begin($item->Field, 'can_')
            || static::begin($item->Field, 'should_');
    }
    protected static function isDateTime($item)
    {
        return static::is($item->Type, 'datetime');
    }


    /**
     * auto filled columns by the Laravel framework or database
     * @return array
     */
    protected static function autoFilledColumns()
    {
        return ['id', 'created_at', 'updated_at'];
    }

    /**
     * call the path builder method
     */
    public function __call($method, $params)
    {
        if (method_exists($this->path_builder, $method)) {
            return $this->path_builder->{$method}(...$params);
        }
    }

    public function __get($name)
    {
        if (property_exists($this->path_builder, $name)) {
            return $this->path_builder->$name;
        }
    }
}
