<?php
namespace Ichikawayac\ReverseScaffoldGenerator\Generators;

use Zend\Code\Generator\ValueGenerator;

class LangGenerator extends BaseGenerator
{
    private $name;
    private $names;

    public function generate()
    {
        $this->name  = static::toTitle($this->valiable_name);
        $this->names = static::toTitle($this->valiables_name);

        $langs = [
            'crud' => [
                'save'   => "Save {$this->name}",
                'create' => "Create {$this->name}",
                'show'   => "Show {$this->name}",
                'edit'   => "Edit {$this->name}",
                'delete' => "Delete {$this->name}",

                'items'   => "{$this->names}",
                'no_item' => "No {$this->names}",

                'created' => "{$this->name} #:id Created",
                'updated' => "{$this->name} #:id Updated",
                'deleted' => "{$this->name} #:id Deleted",

                'back'     => "Back",
                'confirm'  => 'Are you sure?',
                'required' => 'Required',
                'optional' => 'Optional',
            ],
            $this->valiable_name => $this->buildLangColumn(),
        ];

        $vg = new ValueGenerator($langs, ValueGenerator::TYPE_ARRAY_SHORT);
        $vg->setIndentation("    ");

        $replaces = [
            'DummyLangs' => $vg->generate(),
        ];
        $stub = static::getStubFile('message.stub');

        $this->fileGenerate($stub, $replaces, $this->getGenerateFilePath());
    }

    public function getGenerateFilePath()
    {
        return $this->getLangFilePath();
    }


    protected function buildLangColumn()
    {
        $base = [
            'id' => 'ID',
            'action' => 'Action',
        ];

        $columns = $this->getFillableFields()->pluck('Field')->reduce(function($carry, $item) {
            $carry[$item] = static::toTitle($item);
            return $carry;
        }, []);

        return array_replace_recursive ($base, $columns);
    }


    protected static function toTitle($str)
    {
        return title_case(str_replace(['_', '-'], ' ', $str));
    }
}
